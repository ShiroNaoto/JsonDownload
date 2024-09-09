<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Controllers
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\KeyController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\RequestController;

Route::get('/user', function (Request $request) {return $request->user();})->middleware('auth:api');

Route::group(['prefix' => 'v1'], function () {

        //Client Authentication
        Route::middleware('client')->group(function () {
            Route::post('/store-json', [KeyController::class, 'storeJson']);
            Route::get('/redis/keys', [KeyController::class, 'getAllRedisKeys']);
            Route::get('/redis/keys/download/term/{key}', [KeyController::class, 'downloadTermRate']);
            Route::get('/redis/keys/download/soa/{key}', [KeyController::class, 'downloadSOA']);
        });
        
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
        Route::post('/users/{id}/access', [RequestController::class, 'createAccess']);

        //User Authentication
        Route::middleware('auth:api')->group(function () {
                Route::apiResource('users', UserController::class);
                Route::post('clients', [ClientController::class, 'createClient']);
                Route::delete('clients/{id}', [ClientController::class, 'deleteClient']);
                Route::put('clients/{id}', [ClientController::class, 'updateClient']);
        });
});