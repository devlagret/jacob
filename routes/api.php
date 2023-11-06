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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Sample API route
Route::get('/profits', [\App\Http\Controllers\SampleDataController::class, 'profits'])->name('profits');
// Route::get('/kolektibilitas', [\App\Http\Controllers\SampleDataController::class, 'kolektibilitas'])->name('kolektibilitas');
Route::post('login', [ApiController::class, 'login']);

