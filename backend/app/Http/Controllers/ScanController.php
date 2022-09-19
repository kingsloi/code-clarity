<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use App\Models\Scan;

use Carbon\Carbon;

class ScanController extends Controller
{
    /**
     * Import scans from Docparser.com. One parser for each type of page.
     *
     * @return void
     */
    public function import()
    {
        $apiToken = config('docparser.key');

        $parsers = array_filter(explode(',', config('docparser.parsers', '')));

        abort_if(! $apiToken || ! $parsers, 422, 'check .env docparser info');

        foreach ($parsers as $parser){
            $url = 'https://api.docparser.com/v1/results/' . $parser . '?api_key=' . $apiToken;

            $response = Http::acceptJson()->get($url);

            $documents = $response->json();

            foreach($documents as $document) {
                Scan::create(['document' => $document]);
            }
        }
    }

    /**
     * Get all scans, and their relationships
     *
     * @param  Request $request
     * @return array
     */
    public function scans(Request $request)
    {
        $scans = Scan::get();

        // the following function(s) digest the filename,
        // reorder the pages back in order 1,2,3,4,5
        // because files are split per parser
        // and became out of order, so we
        // reorder them, and attach
        // OCR object for ease

        $files = $scans->map(function ($scan) {
            $document = $scan->document;

            $filename = $document->file_name;
            $filename = Str::replace('pdfdeskew-com-page-', '', $filename);
            $filename = Str::replace('.pdf', '', $filename);
            $filename = Str::replace('page', '', $filename);

            // pages were scanned in groups of 90 pages
            // as I was worried the PDF/scanner app
            // would crash, so extract em
            [$section, $page] = array_map(
                function($value) {
                    return implode('-', $value);
                },
                array_chunk(explode('-', $filename), 2)
            );

            // extract the start, and end from string such as "1-90"
            [ $start, $end ] = explode('-', $section);

            return [
                intval($start), intval($end), intval($page)
            ];
        })

        // group by the section's starting number
        ->groupBy(function($parts) {
            return $parts[0];
        })

        // sort them in order (1, 91, 181)
        ->sortKeys()

        // get the page number, sort them in order
        ->map(function($pages) {
            return $pages->map(function($parts) {
                return $parts[2];
            })->sort();
        })

        // now the models are in order,
        // rebuild the file names
        ->map(function($pages, $section) {

            // scanned 214 pages, in chucks of 90 pages per pdf
            $sections = [1 => '1-90', 91 => '91-180', 181=> '181-214'];

            return $pages->map(function($page) use ($section, $sections) {
                return 'pdfdeskew-com-page-' . $sections[$section] .'-page'. $page .'.pdf';
            });
        })

        // now with everything in order, and with docparser
        // saving our original filename, get scans + ocr
        ->map(function($section) {
            return $section->map(function($filename) {
                $scan = Scan::with(['page', 'page.statement'])->whereJsonContains('document->file_name', $filename)->first();

                $document = $scan->document;

                $charges = [];
                $payments = [];

                if (isset($document->charges) && isset($document->payments)) {
                    $charges = $document->charges;
                    $payments = $document->payments;
                } else if (isset($document->charges) && isset($document->table_data)) {
                    $charges = $document->charges;
                    $payments = $document->table_data;
                } else if (isset($document->payments) && isset($document->table_data)) {
                    $charges = $document->table_data;
                    $payments = $document->payments;
                } else if (isset($document->charges) && ! isset($document->payment) && ! isset($document->table_data)) {
                    $charges = $document->charges;
                } else {
                    $charges = [];
                    $payments = [];
                }

                if (isset($document->table_data)) {
                    unset($document->table_data);
                }

                $charges = $this->formatCharges($charges);
                $document->charges = $charges;

                $payments = $this->formatPayments($payments);
                $document->payments = $payments;

                $scan->document = $document;

                return $scan;
            });
        });

        return array_merge(...array_values($files->toArray()));
    }

    /**
     * Format the charges provided by OCR
     *
     * @param  array $charges
     * @return Collection
     */
    public function formatCharges($charges) {
        return collect($charges)

        ->filter(function ($c) {
            return (
                (isset($c->key_0) && trim($c->key_0) !== '') &&
                (isset($c->key_2) && trim($c->key_2) !== '') &&
                (isset($c->key_3) && trim($c->key_3) !== '') &&
                (isset($c->key_4) && trim($c->key_4) !== '') &&
                (isset($c->key_5) && trim($c->key_5) !== '')
            );
        })

        ->map(function ($c) {

            $charge = [];

            try {
                $charge['key_0'] = Carbon::parse($c->key_0)->format('d/m/Y');
            } catch (\Exception $err) {
                $charge['key_0'] = $c->key_0;
            }

            $charge['key_1'] = Str::remove('|', $c->key_1);

            $code = $c->key_2;
            $charge['key_2'] = Str::remove('|', $code);

            $description = $c->key_3;
            $description = Str::remove('|', $description);
            $charge['key_3'] = $description;

            $charge['key_4'] = intval($c->key_4);

            if (isset($c->key_5)) {
                $charge['key_5'] = floatval($c->key_5);
            }

            return $charge;
        })->toArray();
    }

    /**
     * Format the payments provided by OCR
     *
     * @param  array $charges
     * @return Collection
     */
    public function formatPayments($payments) {
        return collect($payments)->map(function ($p) {

            $payment = [];

            try {
                $payment['key_0'] = Carbon::parse(Str::remove('|', $p->key_0))->format('d/m/Y');
            } catch (\Exception $err) {
                $payment['key_0'] = $p->key_0;
            }

            $payment['key_1'] = Str::remove('|', $p->key_1);

            $payment['key_2'] = (isset($p->key_2) ? floatval($p->key_2) : floatval(0));

            return $payment;
        })->toArray();
    }
}
