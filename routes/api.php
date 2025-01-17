<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth-jwt');

#TRIP
Route::post('/addTrip', [TripController::class, 'addTrip'])->middleware('auth-jwt');
Route::get('/showTrip/{id}', [TripController::class, 'showTrip'])->middleware('auth-jwt');
Route::put('/updateTrip/{id}', [TripController::class, 'updateTrip'])->middleware('auth-jwt');
Route::delete('/deleteTrip/{id}', [TripController::class, 'deleteTrip'])->middleware('auth-jwt');

Route::group(['prefix' => 'users'], function ($router) {
    Route::get('{id}', [UserController::class, 'show'])->middleware('auth-jwt');
    Route::patch('{id}', [UserController::class, 'update'])->middleware('auth-jwt');
    Route::delete('{id}', [UserController::class, 'destroy'])->middleware('auth-jwt');
    Route::post('{id}/update-profile-img', [UserController::class, 'updateProfileImage'])->middleware('auth-jwt');
});
