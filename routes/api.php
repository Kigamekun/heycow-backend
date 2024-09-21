<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/auth/register', [\App\Http\Controllers\API\AuthController::class, 'register']);
Route::post('/auth/login', [\App\Http\Controllers\API\AuthController::class, 'login']);

Route::get('/getFile/{folder}/{filename}', function ($folder,$filename) {
    return response()->file(storage_path('app/public/').$folder.'/'.$filename);
});
