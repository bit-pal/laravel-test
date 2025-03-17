<?php

namespace App\Http\Controllers;

use App\Models\TimeSlot;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function getAvailableSlots(Request $request)
    {
        $user = $request->user();
        $maxWeeks = $request->input('max_weeks', 3);

        $slots = $this->bookingService->getAvailableSlots($user, $maxWeeks);

        return response()->json($slots);
    }

    public function bookSlot(Request $request)
    {
        $user = $request->user();
        $slotId = $request->input('slot_id');

        $slot = TimeSlot::findOrFail($slotId);

        try {
            $booking = $this->bookingService->bookSlot($user, $slot);
            return response()->json($booking);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function cancelBooking(Request $request)
    {
        $user = $request->user();
        $bookingId = $request->input('booking_id');

        $booking = $user->bookings()->findOrFail($bookingId);
        $slot = $booking->timeSlot;

        // Cancel the booking
        $booking->delete();
        $slot->update(['is_booked' => false]);

        return response()->json(['message' => 'Booking cancelled successfully']);
    }
} 