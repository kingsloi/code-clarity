<?php

use Illuminate\Support\Facades\Route;

use App\Models\Statement;
use App\Models\Page;
use App\Models\Scan;

use App\Http\Controllers\ScanController;

Route::get('/', function () {
    return Statement::with(['pages', 'pages.charges', 'pages.payments'])->get();
});

Route::get('/import', [ScanController::class, 'import']);

Route::get('/scans/{scanId}/pdf', function (Request $request, $scanId) {
    $scan = Scan::find($scanId);

    // Get the scanned document's original filename
    $filename = $scan->document->file_name;

    // by default, grab the Docparse.com media link to view the PDF
    $url = $scan->document->media_link;

    // Docparse.com deletes files after x (30 days), so as to not lose
    // the relationships between previously entered statements
    // we re-upload the documents to docparser, and save
    // the new files to the another table, so we can
    // still access the docparser media_link as
    // this preserves the OCR - meaning it's
    // clickable/selectable text
    $rescan = DB::table('refresh_scans')->whereJsonContains('document->file_name', $filename)->first();

    // if a rescan has been found
    if ($rescan) {
        $json = json_decode($rescan->document);
        $url = $json->media_link;
    }

    try {
        $data = file_get_contents($url);
    } catch (Exception $e) {
        $data = file_get_contents(public_path('pdfs/' . $filename));
    }

    header('Content-Type: application/pdf');

    echo $data;

    exit;
});
