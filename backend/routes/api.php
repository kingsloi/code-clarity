<?php

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

use App\Models\Statement;
use App\Models\Page;
use App\Models\Charge;
use App\Models\Payment;
use App\Models\Scan;

use App\Http\Controllers\ScanController;


//
// GET ALL SCANS
//
Route::get('/scans', [ScanController::class, 'scans']);

//
// LINK A PAGE TO A SCAN
//
Route::put('/pages/{pageId}/scan/{scanId}', function (Request $request, int $pageId, int $scanId) {
    $page = Page::find($pageId);

    if ($scanId === $page->scanId) {
        $page->update([ 'scanId' => null ]);
    } else {
        $page->update([ 'scanId' => $scanId ]);
    }

    return $page;
});



//
// GET ALL STATEMENTS
//
Route::get('/statements', function (Request $request) {
    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// CREATE A STATEMENT
//
Route::post('/statements', function (Request $request) {
    $statement = Statement::create();

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// UPDATE STATEMENT
//
Route::put('/statements/{statementId}', function (Request $request, int $statementId) {
    $statement = Statement::find($statementId);

    $statement->update($request->only([
        'accountId',
        'accountClass',
        'serviceDate',
        'visitId',
        'attendingPhysician',
        'totalBalance',
        'totalPayments',
        'totalCharges',
        'totalPages'
    ]));

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// DELETE A STATEMENT
//
Route::delete('/statements/{statementId}', function (Request $request, int $statementId) {
    $statement = Statement::find($statementId);

    if ($statement) {
        $statement->delete();
    }

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// CREATE NEW PAGE
//
Route::post('/statements/{statementId}/page', function (Request $request, int $statementId) {
    $page = Page::where('statementId', $statementId)->max('pageNumber') + 1;

    $statement = Statement::find($statementId);

    if ($statement) {
        $statement->pages()->create(['pageNumber' => $page]);
    }

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// DELETE A PAGE
//
Route::delete('/pages/{pageId}', function (Request $request, int $pageId) {
    $page = Page::find($pageId);

    if ($page) {
        $page->delete();
    }

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// CREATE A CHARGE
//
Route::post('/pages/{pageId}/charge', function (Request $request, int $pageId) {
    $page = Page::find($pageId);

    if ($page) {
        $page->charges()->create([]);
    }

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// UPDATE A CHARGE
//
Route::put('/charges/{chargeId}', function (Request $request, int $chargeId) {
    return Charge::find($chargeId)->update($request->toArray());
});


//
// DELETE A CHARGE
//
Route::delete('/charges/{chargeId}', function (Request $request, int $chargeId) {
    $charge = Charge::find($chargeId);

    if ($charge) {
        $charge->delete();
    }

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// CREATE A PAYMENT
//
Route::post('/pages/{pageId}/payment', function (Request $request, int $pageId) {
    $page = Page::find($pageId);

    if ($page) {
        $page->payments()->create([]);
    }

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// UPDATE A PAYMENT
//
Route::put('/payments/{paymentId}', function (Request $request, int $paymentId) {
    return Payment::find($paymentId)->update($request->toArray());
});


//
// DELETE A PAYMENT
//
Route::delete('/payments/{paymentId}', function (Request $request, int $paymentId) {
    $payment = Payment::find($paymentId);

    if ($payment) {
        $payment->delete();
    }

    return Statement::with(['pages', 'pages.charges', 'pages.payments', 'pages.scan'])->get();
});


//
// AUTOCOMPLETE
//
Route::get('/autocomplete/{type}/{column}/{query?}', function (Request $request, $type, $column, $query = null) {
    if ($type === 'charge') {
        $model = new Charge;
    } else if ($type === 'payment') {
        $model = new Payment;
    } else if ($type === 'statement') {
        $model = new Statement;
    }

    if ($query) {
        return $model->select($column)->where($column, 'LIKE', '%'. $query . '%')->distinct()->get();
    }

    return $model->select($column)->get();
});
