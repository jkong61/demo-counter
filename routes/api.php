<?php

use App\Http\Controllers\CounterUserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\LoginSessionController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\SaleController;

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

Route::middleware(['auth:api'])->group(function () {
    Route::get('/feedback/{feedback}', [FeedbackController::class, 'show']);
});

Route::middleware(['validtoken'])->group(function () {
    Route::post('/loginsession', [LoginSessionController::class, 'store']);
    Route::post('/counteruser', [CounterUserController::class, 'store']);
    Route::post('/salesreceipt', [SaleController::class, 'store']);
});

Route::post('/feedback', [FeedbackController::class, 'store']);
