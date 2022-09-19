<?php

use Illuminate\Support\Facades\Route;

use App\Models\Statement;
use App\Models\Page;
use App\Models\Scan;

use App\Http\Controllers\ScanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/import', [ScanController::class, 'import']);

Route::get('/', function () {

    $statement = Statement::create();
    $pages = Page::all();
    $page = Statement::with(['pages', 'pages.charges', 'pages.payments'])->first();

    dd($page->toArray());
});


Route::get('/scans/{scanId}/pdf', function (Request $request, $scanId) {
    $scan = Scan::find($scanId);

    $filename = $scan->document->file_name;
    $test = DB::table('refresh_scans')->whereJsonContains('document->file_name', $filename)->first();
    $json = json_decode($test->document);
    $url = $json->media_link;
    $data = file_get_contents($url);

    // $url = $scan->document->file_name;
    // $data = file_get_contents(public_path('pdfs/' . $url));

    header('Content-Type: application/pdf');

    echo $data;

    exit;
});
