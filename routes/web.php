<?php

use App\Http\Controllers\{ProfileController, FarmController, IOTDevicesController, CattleController, UserController, BlogPostController, TransactionController};
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;


Route::get('/', [LandingPageController::class, 'indexlp']);

Route::get('/welcome', function () {
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



Route::prefix('iotdevice')->group(function () {
    Route::get('/', [IOTDevicesController::class, 'index'])->name('iotdevice.index');
    Route::get('/{id}', [IOTDevicesController::class, 'detail'])->name('iotdevice.detail');
    Route::post('/store', [IOTDevicesController::class, 'store'])->name('iotdevice.store');
    Route::put('/update/{id}', [IOTDevicesController::class, 'update'])->name('iotdevice.update');
    Route::delete('/delete/{id}', [IOTDevicesController::class, 'destroy'])->name('iotdevice.delete');
});



Route::prefix('cattle')->group(callback: function () {
    Route::get('/', [CattleController::class, 'index'])->name('cattle.index');
    Route::get('/{id}', [CattleController::class, 'detail'])->name('cattle.detail');
    Route::post('/store', [CattleController::class, 'store'])->name('cattle.store');
    Route::put('/update/{id}', [CattleController::class, 'update'])->name('cattle.update');
    Route::delete('/delete/{id}', [CattleController::class, 'destroy'])->name('cattle.delete');
});

Route::prefix('user')->group(function () { # untuk gabungan
    Route::get('/', [UserController::class, 'index'])->name('user.index');
    Route::get('/{id}', [UserController::class, 'detail'])->name('user.detail');
    Route::post('/store', [UserController::class, 'store'])->name('user.store');
    Route::put('/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
});

Route::prefix('blog')->group(function () {
    Route::get('/', [BlogPostController::class, 'index'])->name('blog.index');
    Route::get('/{id}', [BlogPostController::class, 'detail'])->name('blog.detail');
    Route::post('/store', [BlogPostController::class, 'store'])->name('blog.store');
    Route::put('/update/{id}', [BlogPostController::class, 'update'])->name('blog.update');
    Route::delete('/delete/{id}', [BlogPostController::class, 'destroy'])->name('blog.delete');
});

// transaction
Route::prefix('transaction')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('transaction.index');
    Route::get('/{id}', [TransactionController::class, 'detail'])->name('transaction.detail');
    Route::post('/store', [TransactionController::class, 'store'])->name('transaction.store');
    Route::put('/update/{id}', [TransactionController::class, 'update'])->name('transaction.update');
    Route::delete('/delete/{id}', [TransactionController::class, 'destroy'])->name('transaction.delete');
});


require __DIR__ . '/auth.php';
