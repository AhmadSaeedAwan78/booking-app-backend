<?php

use App\Http\Controllers\BookingController;
use Illuminate\Http\Request;
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
Route::group(['prefix'=>'slots'], function () {
    Route::get('/all/{serviceId}', [BookingController::class, 'getAvailableSlots'])->name('slot.all');
    Route::post('/book',           [BookingController::class, 'createBooking'])->name('slot.book');
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});