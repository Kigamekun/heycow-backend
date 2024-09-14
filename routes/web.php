<?php

use App\Http\Controllers\{ProfileController, FarmController};
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




Route::prefix('farm')->group(function () {
    Route::get('/', [FarmController::class, 'index'])->name('farm.index');
    Route::get('/{id}', [FarmController::class, 'detail'])->name('farm.detail');
    Route::post('/store', [FarmController::class, 'store'])->name('farm.store');
    Route::put('/update/{id}', [FarmController::class, 'update'])->name('farm.update');
    Route::delete('/delete/{id}', [FarmController::class, 'destroy'])->name('farm.delete');
});


require __DIR__.'/auth.php';
