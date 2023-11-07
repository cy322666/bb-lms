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

Route::post('doc/sms/agreement', [DocController::class, 'agreement']);

Route::post('doc/lead/info', [DocController::class, 'agreement']);

Route::post('doc/sms/check', [DocController::class, 'agreement']);


