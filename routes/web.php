<?php

use App\Http\Controllers\CounterUserController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
    return Auth::check() 
    ? redirect('dashboard')
    : view('fm-welcome');
});

Route::middleware('auth')->group( function () {
    Route::prefix('/dashboard')->group( function () {
        Route::get('/', [OverviewController::class,'index'])->name('dashboard');
        Route::get('/user/{user}', [OverviewController::class,'view_user'])->name('dashboard.view_user');
        Route::get('/counter/{counter}', [OverviewController::class,'view_counter'])->name('dashboard.view_counter');
        Route::get('/{any}', function () {
            return redirect('dashboard');
        })->where('any', '.*');
    });

    // Route::prefix('/counteruser')->group( function () {
    //     Route::get('/', [CounterUserController::class,'index'])->name('counteruser.index');
    //     Route::get('/show/{counterUser}', [CounterUserController::class,'show'])->name('counteruser.show');
    //     Route::get('/{any}', function () {
    //         return redirect('counteruser.index');
    //     })->where('any', '.*');
    // });

    Route::prefix('feedback')->group( function () {
        Route::get('/', [FeedbackController::class,'index'])->name('feedback.index');
        // Route::get('/show/{feedback}', [FeedbackController::class,'show'])->name('feedback.show');
        Route::get('/edit/{feedback}', [FeedbackController::class,'edit'])->name('feedback.edit');
        Route::put('/update/{feedback}', [FeedbackController::class,'update'])->name('feedback.update');
        Route::delete('/delete/{feedback}', [FeedbackController::class,'destroy'])->name('feedback.delete');
        Route::get('/{any}', function () {
            return redirect('feedback.index');
        })->where('any', '.*');
    });
});

require __DIR__.'/auth.php';
