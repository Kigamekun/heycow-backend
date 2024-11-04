<?php

use App\Http\Controllers\Api\FarmControllerApi;
use App\Http\Controllers\Api\CattleControllerApi;
use App\Http\Controllers\Api\IOTDevicesControllerApi;
use App\Http\Controllers\Api\ReplyControllerApi;
use App\Http\Controllers\Api\SubscriptionControllerApi;
use App\Http\Controllers\Api\CommentControllerApi;
use App\Http\Controllers\Api\BlogPostControllerApi;
use App\Http\Controllers\Api\HealthRecordControllerApi;
use App\Http\Controllers\Api\TransactionControllerApi;
use App\Http\Controllers\Api\BreedControllerApi;
use App\Http\Controllers\Api\UserControllerApi;
use App\Http\Controllers\Api\HelpCenterControllerApi;
use App\Http\Controllers\Api\LikeControllerApi;
use Illuminate\Support\Facades\Route;
use App\Models\{Cattle, IOTDevices, Farm};
use Illuminate\Http\Request;



Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/dashboard', function () {
        $userId = auth()->user()->id;
        $cattleSick = Cattle::where('user_id', $userId)->where('status', 'sakit')->count();
        $cattleHealthy = Cattle::where('user_id', $userId)->where('status', 'sehat')->count();
        $cattleDead = Cattle::where('user_id', $userId)->where('status', 'mati')->count();
        $iotDevices = IOTDevices::where('user_id', $userId)->count();
        $pengangon = 4;
        $farm = Farm::where('user_id', $userId)->first();
        $cattle_count = Cattle::where('user_id', $userId)->count();
        $cattle = Cattle::with([
            'breed',
            'iotDevice',
        ])
            ->where('user_id', $userId)
            ->limit(5)
            ->get()
            ->map(function ($cattle) {
                $hr = DB::table('health_records')->where('cattle_id', $cattle->id)->orderBy('created_at', 'desc')->first();
                return [
                    'id' => $cattle->id,
                    'name' => $cattle->name,
                    'status' => $cattle->status,
                    'user_id' => $cattle->user_id,
                    'gender' => $cattle->gender,
                    'type' => $cattle->type,
                    'birth_date' => $cattle->birth_date,
                    'breed' => $cattle->breed->name ?? 'Unknown',
                    'breed_id' => $cattle->breed_id,
                    'birth_weight' => $cattle->birth_weight ?? 'Unknown',
                    'birth_height' => $cattle->birth_height ?? 'Unknown',
                    'iot_devices' => $cattle->iotDevice,
                    'latest_health_status' => $hr
                ];
            });

        return response()->json([
            'cattle_sick' => $cattleSick,
            'cattle_healthy' => $cattleHealthy,
            'cattle_dead' => $cattleDead,
            'iot_devices' => $iotDevices,
            'pengangon' => $pengangon,
            'farm' => $farm,
            'cattle_count' => $cattle_count,
            'cattle' => $cattle
        ]);
    });

    // Rute untuk Farms
    Route::prefix('farms')->group(function () {
        Route::get('/', [FarmControllerApi::class, 'index']);
        Route::post('/', [FarmControllerApi::class, 'store'])->middleware('checkRole:admin');
        Route::get('/{id}', [FarmControllerApi::class, 'show']);
        Route::put('/{id}', [FarmControllerApi::class, 'update']);
        Route::delete('/{id}', [FarmControllerApi::class, 'destroy'])->middleware('checkRole:admin');
        Route::get('/cattle/{id}', [FarmControllerApi::class, 'cattle'])
            ->where('id', '[0-9]+');
        Route::get('/cattle/most-cattle', [FarmControllerApi::class, 'mostCattle']);

    });

    // Rute untuk Cattle
    Route::prefix('cattle')->group(function () {
        Route::get('/', [CattleControllerApi::class, 'index']);
        Route::post('/', [CattleControllerApi::class, 'store']);
        Route::get('/{id}', [CattleControllerApi::class, 'show']);
        Route::put('/{id}', [CattleControllerApi::class, 'update']);
        Route::delete('/{id}', [CattleControllerApi::class, 'destroy']);
        Route::post('/iot-devices/search', [CattleControllerApi::class, 'searchIOT']);
        Route::patch('/assign-iot-devices/{id}', [CattleControllerApi::class, 'assignIOTDevices'])->name('cattle.assign-iot-devices');
        Route::delete('/remove-iot-devices/{id}', [CattleControllerApi::class, 'removeIOTDevices'])->name('cattle.remove-iot-devices');
        Route::patch('change-status/{id}', [CattleControllerApi::class, 'changeStatus'])->name('cattle.change-status');
        Route::post('/create-request', [CattleControllerApi::class, 'createRequest'])->name('cattle.create-request');
        Route::patch('/respond-request/{id}', [CattleControllerApi::class, 'respondToRequest'])->name('cattle.respond-request');
        Route::post('/complete-contract/{id}', [CattleControllerApi::class, 'completeContract'])->name('cattle.complete-contract');

    });

    // Rute untuk IoT Devices
    Route::prefix('iot_devices')->group(function () {
        Route::get('/', [IOTDevicesControllerApi::class, 'index']);
        Route::post('/', [IOTDevicesControllerApi::class, 'store']);
        Route::get('/{id}', [IOTDevicesControllerApi::class, 'show'])->where('id', '[0-9]+');
        Route::put('/{id}', [IOTDevicesControllerApi::class, 'update'])->middleware('checkRole:admin');
        Route::delete('/{id}', [IOTDevicesControllerApi::class, 'destroy'])->middleware('checkRole:admin');
        Route::post('/assign-iot-devices', [IOTDevicesControllerApi::class, 'AssignIOTDevices']);
        Route::put('/change-status/{id}', [IOTDevicesControllerApi::class, 'changeStatus']);
        Route::get('/get-iot-devices-by-user', [IOTDevicesControllerApi::class, 'getIOTDevicesByUser']);
    });

    // Rute untuk Subscription
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionControllerApi::class, 'index']);
        Route::post('/', [SubscriptionControllerApi::class, 'store']);
        Route::get('/{id}', [SubscriptionControllerApi::class, 'show']);
        Route::put('/{id}', [SubscriptionControllerApi::class, 'update']);
        Route::delete('/{id}', [SubscriptionControllerApi::class, 'destroy']);
        Route::get('/status/{userId}', [SubscriptionControllerApi::class, 'checkStatus']);
        Route::get('/plans', [SubscriptionControllerApi::class, 'listPlans']);
        Route::post('/pay', [SubscriptionControllerApi::class, 'initiatePayment']);
        Route::get('/transactions/{userId}', [SubscriptionControllerApi::class, 'transactionHistory']);
        Route::put('/transaction-status/{transactionId}', [SubscriptionControllerApi::class, 'updateSubscriptionStatus']);
    });

    // Rute untuk Blog Posts
    Route::prefix('blog-posts')->group(function () {
        Route::get('/', [BlogPostControllerApi::class, 'index']);
        Route::post('/', [BlogPostControllerApi::class, 'store']);
        Route::get('/{id}', [BlogPostControllerApi::class, 'show']);
        Route::put('/{id}', [BlogPostControllerApi::class, 'update']);
        Route::delete('/{id}', [BlogPostControllerApi::class, 'destroy']);
        // Route::get('/{id}/comments', [CommentControllerApi::class, 'index']);

        // get by 
        

        // Komentar API
        Route::get('/{id}/comments', [CommentControllerApi::class, 'index']);
        Route::post('/{id}/comments', [CommentControllerApi::class, 'store']);
        Route::get('/{id}/comments/{comment_id}', [CommentControllerApi::class, 'show']);
        Route::put('/{id}/comments', [CommentControllerApi::class, 'update']);
        Route::delete('/{id}/comments', [CommentControllerApi::class, 'destroy']);

        // API LIKE
        Route::post('/{id}/likes', [LikeControllerApi::class, 'index']);
        Route::post('/{id}/likes', [LikeControllerApi::class, 'store']);
        Route::get('/{id}/likes/{like_id}', [LikeControllerApi::class, 'show']);
        Route::put('/{id}/likes', [LikeControllerApi::class, 'update']);

        // Forum Category
        Route::get('/forum', [BlogPostControllerApi::class, 'showForumPosts']);

        // Jual Category
        Route::get('/jual', [BlogPostControllerApi::class, 'showJualCategory']);

        // Reply API
        Route::get('/{id}/comments/{comment_id}/reply', [ReplyControllerApi::class, 'index']);
        Route::post('/{id}/comments/{comment_id}/reply', [ReplyControllerApi::class, 'store']);
        Route::get('/{id}/comments/{comment_id}/reply/{reply_id}', [ReplyControllerApi::class, 'show']);
        Route::put('/{id}/comments/{comment_id}/reply', [ReplyControllerApi::class, 'update']);
        Route::delete('/{id}/comments/{comment_id}/reply', [ReplyControllerApi::class, 'destroy']);

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
        Route::get('/', [TransactionControllerApi::class, 'index']);
        Route::get('/{id}', [TransactionControllerApi::class, 'show']);
        Route::put('/{id}', [TransactionControllerApi::class, 'update']);
        Route::delete('/{id}', [TransactionControllerApi::class, 'destroy']);
        Route::put('/{id}/confirm', [TransactionControllerApi::class, 'confirm']);
        Route::get('/user/{userId}', [TransactionControllerApi::class, 'getUserTransactions']);
    });
    

    // Rute untuk Breeds
    Route::prefix('breeds')->group(function () {
        Route::get('/', [CattleControllerApi::class, 'getBreeds']);
        Route::post('/', [BreedControllerApi::class, 'store']);
        Route::delete('/{id}', [BreedControllerApi::class, 'destroy']);
    });

    // Rute untuk Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserControllerApi::class, 'index']);
        Route::post('/', [UserControllerApi::class, 'store']);
        Route::get('/{id}', [UserControllerApi::class, 'show']);
        Route::put('/{id}', [UserControllerApi::class, 'update']);
        Route::delete('/{id}', [UserControllerApi::class, 'destroy']);
        Route::post('/forgot-password', [UserControllerApi::class, 'forgotPassword']);
        Route::post('/change-password', [UserControllerApi::class, 'changePassword']);
        Route::post('/request-iot', [UserControllerApi::class, 'requestIot']);
        Route::post('/assign-farm/{userId}', [UserControllerApi::class, 'assignFarm']);
    });

    // Rute untuk Help Center
    Route::prefix('help_centers')->group(function () {
        Route::get('/', [HelpCenterControllerApi::class, 'index']);
        Route::post('/', [HelpCenterControllerApi::class, 'store']);
        Route::delete('/{id}', [HelpCenterControllerApi::class, 'destroy']);
    });

    Route::post('/uploadFile', function (Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('public/uploads');
            return response()->json(['status' => 'sukses', 'path' => $path]);
        }
        return response()->json(['status' => 'gagal', 'pesan' => 'File tidak ditemukan'], 400);
    });
});

Route::post('/login', [UserControllerApi::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/me', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/register', [\App\Http\Controllers\API\AuthController::class, 'register']);
Route::post('/auth/login', [\App\Http\Controllers\API\AuthController::class, 'login']);

Route::get('/getFile/{folder}/{filename}', function ($folder, $filename) {
    return response()->file(storage_path('app/public/') . $folder . '/' . $filename);
});
