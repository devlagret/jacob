<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Sample API route
Route::group(['middleware'=> ['auth:sanctum']], function(){
Route::get('/profits', [\App\Http\Controllers\SampleDataController::class, 'profits'])->name('profits');
Route::post('test', [ApiController::class, 'tst']);
Route::post('getDepositoAccount', [ApiController::class, 'getDataDeposito']);
Route::post('getCreditsAccount', [ApiController::class, 'getDataCredit']);
Route::post('PostCreditsById/{credits_account_id}', [ApiController::class, 'PostCreditsById']);
Route::post('getMembers', [ApiController::class, 'getDataMembers']);
Route::post('PostSavingsById/{savings_account_id}', [ApiController::class, 'PostSavingsById']);
Route::post('PostSavingsByNo/{savings_account_no}', [ApiController::class, 'PostSavingsByNo']);
Route::post('PostSavingsByMember/{member_id}', [ApiController::class, 'PostSavingsByMember']);
Route::post('PrintmutationByMember/{member_id}', [ApiController::class, 'PrintmutationByMember']);
Route::post('PostSavingsmutation', [ApiController::class, 'GetDeposit']);
Route::post('GetWithdraw', [ApiController::class, 'GetWithdraw']);
//save simp wajib
Route::post('processAddMemberSavings/{member_id?}', [ApiController::class, 'processAddMemberSavings']);

Route::post('getDataCredit', [ApiController::class, 'getDataCredit']);
Route::post('PostCreditsById', [ApiController::class, 'PostCreditsById']);

//save angsuran
Route::post('processAddCreditsPaymentCash/{credit_account_id?}', [ApiController::class, 'processAddCreditsPaymentCash']);

Route::post('logout', [ApiController::class, 'logout']);
Route::post('getLoginState', [ApiController::class, 'getLoginState']);

//print
Route::post('printer-address', [APIController::class, 'printerAddress']);
Route::post('printer-address/update', [APIController::class, 'updatePrinterAddress']);

Route::prefix('saving')->controller(ApiController::class)->group(function () {
    Route::get('account','getDataSavings');
    Route::post('deposit/{savings_account_id?}','deposit');
    Route::post('withdraw/{savings_account_id?}','withdraw');
});
});


Route::post('login', [ApiController::class, 'login']);

