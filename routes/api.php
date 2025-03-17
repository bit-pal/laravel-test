<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ScoringController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Scoring routes
    Route::get('/matched-users', [ScoringController::class, 'getMatchedUsers']);
    Route::get('/matched-co-investors', [ScoringController::class, 'getMatchedCoInvestors']);

    // Booking routes
    Route::get('/available-slots', [BookingController::class, 'getAvailableSlots']);
    Route::post('/book-slot', [BookingController::class, 'bookSlot']);
    Route::delete('/cancel-booking', [BookingController::class, 'cancelBooking']);
}); 