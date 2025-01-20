<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\ListsController;
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

Route::group(['prefix' => 'users'], function ($router) {
    Route::get('{id}', [UserController::class, 'show'])->middleware('auth-jwt');
    Route::patch('{id}', [UserController::class, 'update'])->middleware('auth-jwt');
    Route::delete('{id}', [UserController::class, 'destroy'])->middleware('auth-jwt');
    Route::post('{id}/update-profile-img', [UserController::class, 'updateProfileImage'])->middleware('auth-jwt');
});

Route::group(['prefix' => 'trips'], function ($router) {
    Route::get('/', [TripController::class, 'getTrips'])->middleware('auth-jwt');
    Route::post('/', [TripController::class, 'addTrip'])->middleware('auth-jwt');
    Route::get('{id}', [TripController::class, 'showTrip'])->middleware('auth-jwt');
    Route::get('/{code}/fetch-trip', [TripController::class, 'fetchTrip'])->middleware('auth-jwt');
    Route::post('/{id}/join', [TripController::class, 'joinTrip'])->middleware('auth-jwt');
    Route::patch('{id}', [TripController::class, 'updateTrip'])->middleware('auth-jwt');
    Route::post('{id}/update-img', [TripController::class, 'updateImage'])->middleware('auth-jwt');
    Route::delete('{id}', [TripController::class, 'deleteTrip'])->middleware('auth-jwt');
    Route::delete('{id}/leave', [TripController::class, 'leaveTrip'])->middleware('auth-jwt');

    Route::get('{idTrip}/events', [EventController::class, 'index'])->middleware('auth-jwt');
    Route::post('{idTrip}/events', [EventController::class, 'create'])->middleware('auth-jwt');
    Route::put('{idTrip}/events/{idEvent}', [EventController::class, 'edit'])->middleware('auth-jwt');
    Route::delete('{idTrip}/events/{idEvent}', [EventController::class, 'destroy'])->middleware('auth-jwt');
    Route::post('{idTrip}/events/{idEvent}/join', [EventController::class, 'joinEvent'])->middleware('auth-jwt');
    Route::post('{idTrip}/events/{idEvent}/leave', [EventController::class, 'leaveEvent'])->middleware('auth-jwt');

    Route::get('{idTrip}/polls', [PollController::class, 'index'])->middleware('auth-jwt');
    Route::post('{idTrip}/polls', [PollController::class, 'create'])->middleware('auth-jwt');
    Route::post('{idTrip}/polls/{idPoll}/vote/{idOption}', [PollController::class, 'vote'])->middleware('auth-jwt');

    Route::get('{idTrip}/lists', [ListsController::class, 'getLists'])->middleware('auth-jwt');
    Route::post('{idTrip}/lists', [EventController::class, 'addLists'])->middleware('auth-jwt');
    Route::delete('{idTrip}/lists/{idLists}', [EventController::class, 'deleteLists'])->middleware('auth-jwt');
});
