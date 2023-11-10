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
Route::post('getMembers', [ApiController::class, 'getDataMembers']);
Route::post('PostSavingsById/{savings_account_id}', [ApiController::class, 'PostSavingsById']);
Route::post('PostSavingsByNo/{savings_account_no}', [ApiController::class, 'PostSavingsByNo']);
Route::post('logout', [ApiController::class, 'logout']);
Route::post('getLoginState', [ApiController::class, 'getLoginState']);

Route::prefix('saving')->middleware(ApiController::class)->group(function () {
    Route::get('account','getDataSavings');
    Route::post('deposit','deposit');
});
});


Route::post('login', [ApiController::class, 'login']);

