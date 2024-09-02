<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Controllers
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\RequestController;

Route::get('/user', function (Request $request) {return $request->user();})->middleware('auth:api');

Route::group(['prefix' => 'v1'], function () {

        Route::middleware('client')->group(function () {
            Route::post('/store-json', [UserController::class, 'storeJson']);
            Route::get('/redis/keys', [UserController::class, 'getAllRedisKeys']);
            Route::get('/redis/keys/download/{key}', [UserController::class, 'downloadRedisKey']);
        });
        
        Route::apiResource('users', UserController::class);
        Route::apiResource('projects', ProjectController::class);
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
        Route::post('/users/{id}/access', [RequestController::class, 'createAccess']);

        //Client routes
        Route::middleware('auth:api')->group(function () {
                Route::post('clients', [ClientController::class, 'createClient']);
                Route::delete('clients/{id}', [ClientController::class, 'deleteClient']);
                Route::put('clients/{id}', [ClientController::class, 'updateClient']);
        });
});