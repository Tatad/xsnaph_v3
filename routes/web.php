<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/manage/xero', [App\Http\Controllers\XeroController::class, 'redirectUserToXero'])->name('redirectUserToXero');
Route::get('/xero/auth/callback', [App\Http\Controllers\XeroController::class, 'handleCallbackFromXero'])->name('handleCallbackFromXero');


Route::get('/switch-organisation', [App\Http\Controllers\HomeController::class, 'switchOrg'])->name('switchOrg');
Route::get('/select-organization/{id}', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');
Route::get('/sales-summary/', [App\Http\Controllers\HomeController::class, 'sales'])->name('sales');
Route::get('/reports-2307-summary/', [App\Http\Controllers\HomeController::class, 'report2307'])->name('report2307');
Route::get('/purchases-summary/', [App\Http\Controllers\HomeController::class, 'purchase'])->name('purchase');
Route::get('/quarterly-slsp-summary/', [App\Http\Controllers\HomeController::class, 'quarterlySLSPSummary'])->name('quarterlySLSPSummary');
Route::get('/get-rdo-codes', [App\Http\Controllers\HomeController::class, 'getRDOCodes'])->name('getRDOCodes');

Route::get('/get-organizations', [App\Http\Controllers\HomeController::class, 'getOrganizations'])->name('getOrganizations');

Route::post('/save-organization', [App\Http\Controllers\HomeController::class, 'saveOrgInfo'])->name('saveOrgInfo');
Route::post('/upload-excel', [App\Http\Controllers\HomeController::class, 'uploadExcel'])->name('uploadExcel');
Route::post('/upload-2307', [App\Http\Controllers\HomeController::class, 'upload2307Report'])->name('upload2307Report');
Route::post('/upload-purchases', [App\Http\Controllers\HomeController::class, 'uploadPurchases'])->name('uploadPurchases');

Route::get('/get-sales-records', [App\Http\Controllers\HomeController::class, 'getSalesRecords'])->name('getSalesRecords');
Route::get('/get-2307-records', [App\Http\Controllers\HomeController::class, 'get2307Records'])->name('get2307Records');
Route::get('/get-purchases-records', [App\Http\Controllers\HomeController::class, 'getPurchasesRecords'])->name('getPurchasesRecords');
Route::get('/get-quarterly-slsp-summary', [App\Http\Controllers\HomeController::class, 'getQuarterlySLSPSummary'])->name('getQuarterlySLSPSummary');

Route::post('/download-quarterly-slsp-summary', [App\Http\Controllers\HomeController::class, 'downloadQuarterlySLSPSummary'])->name('downloadQuarterlySLSPSummary');



Route::get('/download-sales/{id}', [App\Http\Controllers\HomeController::class, 'downloadSales'])->name('downloadSales');
Route::get('/download-purchase/{id}', [App\Http\Controllers\HomeController::class, 'downloadPurchase'])->name('downloadPurchase');
Route::get('/refresh-token', [App\Http\Controllers\XeroController::class, 'refreshAccessTokenIfNecessary'])->name('refreshAccessTokenIfNecessary');

Route::post('/delete-purchases', [App\Http\Controllers\HomeController::class, 'deletePurchases'])->name('deletePurchases');
Route::post('/delete-sales-record', [App\Http\Controllers\HomeController::class, 'deleteSalesRecords'])->name('deleteSalesRecords');
Route::post('/delete-2307-records', [App\Http\Controllers\HomeController::class, 'delete2307Records'])->name('delete2307Records');

Route::post('/remove-sales-record', [App\Http\Controllers\HomeController::class, 'removeSalesRecord'])->name('removeSalesRecord');
Route::post('/remove-purchase-record', [App\Http\Controllers\HomeController::class, 'removePurchaseRecord'])->name('removePurchaseRecord');
Route::post('/remove-2307-record', [App\Http\Controllers\HomeController::class, 'remove2307Record'])->name('remove2307Record');

Route::get('/download-2307/{id}', [App\Http\Controllers\HomeController::class, 'generate2307'])->name('generate2307');
Route::post('/download-multiple-2307', [App\Http\Controllers\HomeController::class, 'generateMultiple2307'])->name('generateMultiple2307');

Route::post('/remove-sales-batch-record', [App\Http\Controllers\HomeController::class, 'removeSalesBatchRecord'])->name('removeSalesBatchRecord');
Route::post('/remove-purchases-batch-record', [App\Http\Controllers\HomeController::class, 'removePurchasesBatchRecord'])->name('removePurchasesBatchRecord');
Route::post('/get-slsp-summary', [App\Http\Controllers\HomeController::class, 'getSLSPRecords'])->name('getSLSPRecords');
Route::post('/download-quarterly-slsp-summary-via-pdf', [App\Http\Controllers\HomeController::class, 'downloadQuarterlySLSPViaPDF'])->name('downloadQuarterlySLSPViaPDF');






