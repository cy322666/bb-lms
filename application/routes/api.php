<?php

use App\Http\Controllers\DocController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('doc/sms/agreement/{accounts:subdomain}', [DocController::class, 'agreement']);

Route::post('doc/lead/info/{accounts:subdomain}', [DocController::class, 'info']);

Route::post('doc/sms/check/{accounts:subdomain}', [DocController::class, 'check']);


