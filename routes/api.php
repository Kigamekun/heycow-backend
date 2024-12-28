<?php

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\FarmControllerApi;
use App\Http\Controllers\Api\CattleControllerApi;
use App\Http\Controllers\Api\IOTDevicesControllerApi;
use App\Http\Controllers\Api\ReplyControllerApi;
use App\Http\Controllers\Api\RequestAngonControllerApi;
use App\Http\Controllers\Api\SubscriptionControllerApi;
use App\Http\Controllers\Api\CommentControllerApi;
use App\Http\Controllers\Api\BlogPostControllerApi;
use App\Http\Controllers\Api\HealthRecordControllerApi;
use App\Http\Controllers\Api\TransactionControllerApi;
use App\Http\Controllers\Api\BreedControllerApi;
use App\Http\Controllers\Api\UserControllerApi;
use App\Http\Controllers\Api\HelpCenterControllerApi;
use App\Http\Controllers\Api\LikeControllerApi;
use App\Http\Controllers\Api\NotificationControllerApi;
use App\Http\Controllers\Api\HistoryRecordControllerApi;
use App\Http\Controllers\Api\ContractControllerApi;
use Illuminate\Support\Facades\Route;
use App\Models\{Cattle, IOTDevices, Farm, RequestAngon};
use Illuminate\Http\Request;


// Route::get('/export-excel', [ExportController::class, 'exportExcel']);
// Route::get('/export-pdf', [ExportController::class, 'exportPdf']);

Route::post('/health_records', [HealthRecordControllerApi::class, 'store']);
Route::get('/cattle/iot-devices/iot/{id}', [CattleControllerApi::class, 'iotDevices']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', function () {
        $userId = auth()->user()->id;
        $cattleSick = Cattle::where('user_id', $userId)->where('status', 'sakit')->count();
        $cattleHealthy = Cattle::where('user_id', $userId)->where('status', 'sehat')->count();
        $cattleDead = Cattle::where('user_id', $userId)->where('status', 'mati')->count();
        $iotDevices = IOTDevices::where('user_id', $userId)->count();
        $contract = RequestAngon::where('user_id',$userId)->where('status','approved')->count();
        $farm = Farm::where('user_id', $userId)->first();
        $cattle_count = Cattle::where('user_id', $userId)->count();
        $notif_count = DB::table('notifications')->where([
            'to_user'=>auth()->user()->id,
            'is_read'=>0
        ])->count();


    $user = Auth::id();


// Base query to retrieve cattle data
$data = Cattle::with(['iotDevice', 'breed', 'farm', 'healthRecords'])
->orderBy('id', 'DESC');

// Search filter
if (isset($_GET['search'])) {
$data = $data->where('name', 'like', '%' . $_GET['search'] . '%');
}

// Role-based filtering for cattle data
if (auth()->user()->role == 'cattleman' && auth()->user()->is_pengangon == 0) {
// Cattle owned by the logged-in user
$data = $data->where('user_id', $user);
} else if (auth()->user()->role == 'cattleman' && auth()->user()->is_pengangon == 1) {

// Cattle either owned by the caretaker or cared for under an active contract
$data = $data->where(function ($query) use ($user) {
    $query->where('user_id', $user) // Owned by the logged-in user
          ->orWhereHas('contracts', function ($query) use ($user) {
              $query->where('status', 'active')
                    ->whereHas('requestAngon', function ($query) use ($user) {
                        $query->where('peternak_id', $user);
                    });
          });
});

}

// Load first health record and adjust farm based on contract status
$data = $data->get()->map(function ($cattle) {
// Add the first health record for each cattle, if exists
$cattle->first_health_record = $cattle->healthRecords()->first();

// Check if cattle has an active contract
$activeContract = $cattle->contracts()->where('status', 'active')->first();
if ($activeContract) {
    $farm = Farm::where('id',$activeContract->farm_id)->first();
    $cattle->farmNow = $farm; // Set farm to caretaker's farm
} else {
    $farm = Farm::where('id',$cattle->farm_id)->first();
    $cattle->farmNow = $farm; // Set farm to caretaker's farm


}
return $cattle;


});

        return response()->json([
            'notif_count' => $notif_count,
            'cattle_sick' => $cattleSick,
            'cattle_healthy' => $cattleHealthy,
            'cattle_dead' => $cattleDead,
            'iot_devices' => $iotDevices,
            'contract' => $contract,
            'farm' => $farm,
            'cattle_count' => $cattle_count,
            'cattle' => $data
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

     // Rute untuk Farms
     Route::prefix('request-angon')->group(function () {
        Route::get('/', [RequestAngonControllerApi::class, 'index']);
        Route::post('/', [RequestAngonControllerApi::class, 'store']);
        Route::put('/{id}/approve', [RequestAngonControllerApi::class, 'approveRequest']);
        Route::put('/{id}/reject', [RequestAngonControllerApi::class, 'rejectRequest']);
        Route::post('/contract/{contractId}/approve', [RequestAngonControllerApi::class, 'approveOrDeclineRequest']);
        Route::post('/contract/{contractId}/pay', [RequestAngonControllerApi::class, 'payForContract']);
    });

    Route::prefix('contract')->group(function () {
        Route::get('/', [ContractControllerApi::class, 'index']);
        Route::get('/{id}', [ContractControllerApi::class, 'show']);
        Route::post('/{contractId}/return', [ContractControllerApi::class, 'returnContract']);

    });

    // Rute untuk Cattle
    Route::prefix('cattle')->group(function () {
        Route::get('/', [CattleControllerApi::class, 'index']);
        Route::post('/', [CattleControllerApi::class, 'store']);
        Route::get('/{id}', [CattleControllerApi::class, 'show']);
        Route::post('/{id}', [CattleControllerApi::class, 'update']);
        Route::delete('/{id}', [CattleControllerApi::class, 'destroy']);
        Route::post('/iot-devices/search', [CattleControllerApi::class, 'searchIOT']);
        Route::patch('/assign-iot-devices/{id}', [CattleControllerApi::class, 'assignIOTDevices']);
        Route::delete('/remove-iot-devices/{id}', [CattleControllerApi::class, 'removeIOTDevices'])->name('cattle.remove-iot-devices');
       });

    // Rute untuk IoT Devices
    Route::prefix('iot_devices')->group(function () {
        Route::get('/', [IOTDevicesControllerApi::class, 'index']);
        Route::post('/', [IOTDevicesControllerApi::class, 'store']);
        Route::get('/{id}', [IOTDevicesControllerApi::class, 'show'])->where('id', '[0-9]+');
        Route::put('/{id}', [IOTDevicesControllerApi::class, 'update'])->middleware('checkRole:admin');
        Route::delete('/{id}', [IOTDevicesControllerApi::class, 'destroy'])->middleware('checkRole:admin');
        Route::post('/assign-iot-devices', [IOTDevicesControllerApi::class, 'AssignIOTDevices']);
        Route::delete('/remove_devices/{id}', [IOTDevicesControllerApi::class, 'removeIOTDevices']);
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
        Route::delete('/{id}/likes', [LikeControllerApi::class, 'unlikePost']);
        // // Forum Category
        // Route::get('/forum', [BlogPostControllerApi::class, 'showForumPosts']);

        // // Jual Category
        // Route::get('/jual', [BlogPostControllerApi::class, 'showJualCategory']);

        // Reply API
        Route::get('/{id}/comments/{comment_id}/reply', [ReplyControllerApi::class, 'index']);
        Route::post('/{id}/comments/{comment_id}/reply', [ReplyControllerApi::class, 'store']);
        Route::get('/{id}/comments/{comment_id}/reply/{reply_id}', [ReplyControllerApi::class, 'show']);
        Route::put('/{id}/comments/{comment_id}/reply', [ReplyControllerApi::class, 'update']);
        Route::delete('/{id}/comments/{comment_id}/reply', [ReplyControllerApi::class, 'destroy']);

    });


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/notifications', [NotificationControllerApi::class, 'getUserNotifications']);
        Route::put('/notifications/{id}/read', [NotificationControllerApi::class, 'markAsRead']);
    });

    // Rute untuk Health Records
    Route::prefix('health_records')->group(function () {
        Route::get('/', [HealthRecordControllerApi::class, 'index']);
        Route::get('/{id}', [HealthRecordControllerApi::class, 'show']);
        Route::put('/{id}', [HealthRecordControllerApi::class, 'update']);
        Route::delete('/{id}', [HealthRecordControllerApi::class, 'destroy']);
        Route::get('/cattle/{id}/monthly-health-records', [HealthRecordControllerApi::class, 'showMonthlyHealthRecords']);

    });

    // Rute untuk History Records
    Route::prefix('history_records')->group(function () {
        Route::get('/', [HistoryRecordControllerApi::class, 'index']);
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
        Route::put('/{id}', [BreedControllerApi::class, 'update']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserControllerApi::class, 'index']);
        Route::post('/', [UserControllerApi::class, 'store']);
        Route::get('/{id}', [UserControllerApi::class, 'show'])->where('id', '[0-9]+');
        Route::put('/{id}', [UserControllerApi::class, 'update']);
        Route::delete('/{id}', [UserControllerApi::class, 'destroy']);
        Route::post('/forgot-password', [UserControllerApi::class, 'forgotPassword']);
        Route::post('/change-password', [UserControllerApi::class, 'changePassword']);
        Route::post('/request-iot', [UserControllerApi::class, 'requestIot']);
        Route::post('/assign-farm/{userId}', [UserControllerApi::class, 'assignFarm']);
        Route::post('/submit-request-form', [UserControllerApi::class, 'submitRequestForm']);
        Route::get('/pengangon', [UserControllerApi::class, 'getUserByPengangon']);
        Route::get('/{id}/detail', [UserControllerApi::class, 'getDetailPengangon']);
        Route::post('/request-ngangon', [UserControllerApi::class, 'requestNgangon']);
        Route::patch('/{userId}/approve', [UserControllerApi::class, 'approveRequest']);
        Route::put('/{userId}/reject', [UserControllerApi::class, 'rejectRequest']);
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



Route::post('/update-profile', function (Request $request) {
    // Validate the incoming request data
    $request->validate([
        'nama' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone_number' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'farm_name' => 'nullable|string|max:255',
        'farm_address' => 'nullable|string|max:255',
        'upah' => 'nullable|numeric',
        'gender' => 'nullable|string|in:male,female',
        'avatar' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
    ]);

    $user = $request->user(); // Get the authenticated user

    // Prepare data for updating
    $data = [
        'name' => $request->input('nama'),
        'email' => $request->input('email'),
        'phone_number' => $request->input('phone_number'),
        'address' => $request->input('address'),
        'gender' => $request->input('gender'),
        'upah' => $request->input('upah'),
    ];

    if ($request->hasFile('avatar')) {
        if ($user->avatar) {
            Storage::delete($user->avatar);
        }


        $path = $request->file('avatar')->store('avatars','public');
        $data['avatar'] = $path;
    }

    $user->update($data);

    $farm = Farm::where('user_id',$user->id)->first();

    if (!is_null($farm)) {
        Farm::where('user_id',$user->id)->update([
            'name'=>$request->farm_name,
            'address'=>$request->farm_address,
        ]);
    }

    else {
        $farm = Farm::create([
            'name'=>$request->farm_name,
            'address'=>$request->farm_address,
            'user_id'=>auth()->user()->id,
        ]);
        Cattle::where('user_id',auth()->user()->id)->update([
            "farm_id"=>$farm->id
        ]);
    }



    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $user,
    ], 200);
})->middleware('auth:sanctum');

Route::post('/auth/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);


Route::get('/me', function (Request $request) {
    $user = $request->user();
    if ($user->avatar && !str_starts_with($user->avatar, 'https')) {
        $user->avatar = url("/api/getFile/{$user->avatar}");
    }
    $user->farm = Farm::where('user_id',auth()->user()->id)->first();
    return $user;
})->middleware('auth:sanctum');

Route::get('/getFile/{folder}/{filename}', function ($folder, $filename) {
    return response()->file(storage_path('app/public/') . $folder . '/' . $filename);
});



