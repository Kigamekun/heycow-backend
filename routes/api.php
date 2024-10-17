<?php

use App\Http\Controllers\Api\FarmControllerApi;
use App\Http\Controllers\Api\CattleControllerApi;
use App\Http\Controllers\Api\IOTDevicesControllerApi;
use App\Http\Controllers\Api\SubscriptionControllerApi;
use App\Http\Controllers\Api\CommentControllerApi;
use App\Http\Controllers\Api\BlogPostControllerApi;
use App\Http\Controllers\Api\HealthRecordControllerApi;
use App\Http\Controllers\Api\TransactionControllerApi;
use App\Http\Controllers\Api\BreedControllerApi;
use App\Http\Controllers\Api\UserControllerApi;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware(['auth:sanctum'])->group(function () {

    // Rute untuk Farms
    Route::prefix('farms')->group(function () {
        Route::get('/', [FarmControllerApi::class, 'index']);
        Route::post('/', [FarmControllerApi::class, 'store'])->middleware('checkRole:admin');
        Route::get('/{id}', [FarmControllerApi::class, 'show']);
        Route::put('/{id}', [FarmControllerApi::class, 'update']);
        Route::delete('/{id}', [FarmControllerApi::class, 'destroy'])->middleware('checkRole:admin');
    });

    // Rute untuk Cattle
    Route::prefix('cattle')->group(function () {
        Route::get('/', [CattleControllerApi::class, 'index']);
        Route::post('/', [CattleControllerApi::class, 'store']);
        Route::get('/{id}', [CattleControllerApi::class, 'show']);
        Route::put('/{id}', [CattleControllerApi::class, 'update']);
        Route::delete('/{id}', [CattleControllerApi::class, 'destroy']);
    });

    // Rute untuk IoT Devices
    Route::prefix('iot_devices')->group(function () {
        Route::get('/', [IOTDevicesControllerApi::class, 'index']);
        Route::post('/', [IOTDevicesControllerApi::class, 'store']);
        Route::get('/{id}', [IOTDevicesControllerApi::class, 'show']);
        Route::put('/{id}', [IOTDevicesControllerApi::class, 'update'])->middleware('checkRole:admin');
        Route::delete('/{id}', [IOTDevicesControllerApi::class, 'destroy'])->middleware('checkRole:admin');
    });

    // Rute untuk Subscription
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionControllerApi::class, 'index']);
        Route::post('/', [SubscriptionControllerApi::class, 'store']);
        Route::get('/{id}', [SubscriptionControllerApi::class, 'show']);
        Route::put('/{id}', [SubscriptionControllerApi::class, 'update']);
        Route::delete('/{id}', [SubscriptionControllerApi::class, 'destroy']);
    });

    // Rute untuk Comments
    Route::prefix('comments')->group(function () {
        Route::get('/', [CommentControllerApi::class, 'index']);
        Route::post('/', [CommentControllerApi::class, 'store']);
        Route::get('/{id}', [CommentControllerApi::class, 'show']);
        Route::put('/{id}', [CommentControllerApi::class, 'update']);
        Route::delete('/{id}', [CommentControllerApi::class, 'destroy']);
    });

    // Rute untuk Blog Posts
    Route::prefix('blog_posts')->group(function () {
        Route::get('/', [BlogPostControllerApi::class, 'index']);
        Route::post('/', [BlogPostControllerApi::class, 'store']);
        Route::get('/{id}', [BlogPostControllerApi::class, 'show']);
        Route::put('/{id}', [BlogPostControllerApi::class, 'update']);
        Route::delete('/{id}', [BlogPostControllerApi::class, 'destroy']);
    });

    // Rute untuk Health Records
    Route::prefix('health_records')->group(function () {
        Route::get('/', [HealthRecordControllerApi::class, 'index']);
        Route::post('/', [HealthRecordControllerApi::class, 'store']);
        Route::get('/{id}', [HealthRecordControllerApi::class, 'show']);
        Route::put('/{id}', [HealthRecordControllerApi::class, 'update']);
        Route::delete('/{id}', [HealthRecordControllerApi::class, 'destroy']);
    });

    // Rute untuk Transactions
    Route::prefix('transactions')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\TransactionControllerApi::class, 'destroy']);
    });

    // Rute untuk Breeds
    Route::prefix('breeds')->group(function () {
        Route::get('/', [BreedControllerApi::class, 'index']);
        Route::post('/', [BreedControllerApi::class, 'store']);
        Route::delete('/{id}', [BreedControllerApi::class, 'destroy']);
    });

    Route::post('/uploadFile', function (Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('public/uploads');
            return response()->json(['status' => 'sukses', 'path' => $path]);
        }
        return response()->json(['status' => 'gagal', 'pesan' => 'File tidak ditemukan'], 400);
    });


    // Rute untuk Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserControllerApi::class, 'index']);
        Route::post('/', [UserControllerApi::class, 'store']);
        Route::get('/{id}', [UserControllerApi::class, 'show']);
        Route::put('/{id}', [UserControllerApi::class, 'update']);
        Route::delete('/{id}', [UserControllerApi::class, 'destroy']);
    });
});

Route::post('/login', [UserControllerApi::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/auth/register', [\App\Http\Controllers\API\AuthController::class, 'register']);
Route::post('/auth/login', [\App\Http\Controllers\API\AuthController::class, 'login']);

Route::get('/getFile/{folder}/{filename}', function ($folder, $filename) {
    return response()->file(storage_path('app/public/') . $folder . '/' . $filename);
});
