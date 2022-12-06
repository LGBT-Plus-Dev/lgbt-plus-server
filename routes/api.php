<?php

use App\Http\Controllers\BarangayController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecialistController;
use App\Http\Controllers\UserController;
use App\Models\Specialist;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('admin')->group(function () {
    Route::post('authenticate', [UserController::class, 'authenticate']);
});

Route::prefix('barangay')->group(function () {
    Route::get('/list', [BarangayController::class, 'getList']);
    Route::get('/{id}', [BarangayController::class, 'getById']);

    Route::post('/', [BarangayController::class, 'create']);
    Route::post('/{id}', [BarangayController::class, 'update']);

    Route::delete('/{id}', [BarangayController::class, 'delete']);
});

Route::prefix('specialist')->group(function () {
    Route::get('/list', [SpecialistController::class, 'getList']);
    Route::get('/{id}', [SpecialistController::class, 'getById']);
    // Route::get('/service/{id}', [SpecialistController::class, 'getSpecialistByService']);
    Route::get('/fifo/{category}', [SpecialistController::class, 'getSpecialistFifo']);
    Route::get('/attendance/{date}', [SpecialistController::class, 'getAttendance']);
    
    Route::post('authenticate', [SpecialistController::class, 'authenticate']);
    Route::post('/', [SpecialistController::class, 'create']);
    Route::post('/{id}', [SpecialistController::class, 'update']);
    
    Route::delete('/{id}', [SpecialistController::class, 'delete']);
});

Route::prefix('client')->group(function () {
    Route::get('/list', [ClientController::class, 'getList']);
    Route::get('/{id}', [ClientController::class, 'getById']);
    
    Route::post('authenticate', [ClientController::class, 'authenticate']);
    Route::post('/', [ClientController::class, 'create']);
    Route::post('/{id}', [ClientController::class, 'update']);
    
    Route::delete('/{id}', [ClientController::class, 'delete']);
});

Route::prefix('service')->group(function () {
    Route::get('/list', [ServiceController::class, 'getList']);
    Route::get('/{id}', [ServiceController::class, 'getById']);
    
    Route::post('/', [ServiceController::class, 'create']);
    Route::post('/{id}', [ServiceController::class, 'update']);
    
    Route::delete('/{id}', [ServiceController::class, 'delete']);
});

Route::prefix('booking')->group(function () {
    Route::get('/list', [BookingController::class, 'getList']);
    Route::get('/today', [BookingController::class, 'getTodaysBooking']);
    Route::get('/today/specialist/{specialist}', [BookingController::class, 'getTodaysBookingBySpecialist']);
    Route::get('/today/client/{client}', [BookingController::class, 'getTodaysBookingByClient']);
    Route::get('/client/{client}', [BookingController::class, 'getClientBookings']);
    Route::get('/specialist/{specialist}', [BookingController::class, 'getSpecialistBookings']);
    Route::get('/declined/{specialist}', [BookingController::class, 'getDeclinedBookings']);
    Route::get('/{id}', [BookingController::class, 'getById']);

    Route::post('/', [BookingController::class, 'create']);
    Route::post('/accept', [BookingController::class, 'acceptBooking']);
    Route::post('/decline', [BookingController::class, 'declineBooking']);
    Route::post('/payment', [BookingController::class, 'addPayment']);
    Route::get('/payment/confirm/{booking}', [BookingController::class, 'confirmPayment']);
    Route::get('/complete/{booking}', [BookingController::class, 'completeBooking']);
});


Route::prefix('chat')->group(function () {
    Route::get('messages/{booking}', [ChatController::class, 'getMessages']);
    Route::post('send', [ChatController::class, 'send']);
});
