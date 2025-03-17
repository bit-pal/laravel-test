<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\TimeSlot;
use App\Services\BookingService;
use Carbon\Carbon;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    private $bookingService;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = new BookingService();
        $this->user = new User();
    }

    public function test_get_available_slots()
    {
        $slots = $this->bookingService->getAvailableSlots($this->user);
        $this->assertIsObject($slots);
    }

    public function test_slot_duration()
    {
        $now = Carbon::now();
        $slot = new TimeSlot([
            'start_time' => $now,
            'end_time' => $now->copy()->addMinutes(15),
        ]);

        $duration = $slot->end_time->diffInMinutes($slot->start_time);
        $this->assertEquals(15, $duration);
    }

    public function test_booking_limit()
    {
        $this->expectException(\Exception::class);
        
        // Create a user with weekly booking limit
        $user = new User(['weekly_booking_limit' => 2]);
        
        // Create and book slots
        for ($i = 0; $i < 3; $i++) {
            $slot = new TimeSlot([
                'start_time' => Carbon::now()->addDays($i),
                'end_time' => Carbon::now()->addDays($i)->addMinutes(15),
                'is_booked' => false,
                'week_number' => Carbon::now()->weekOfYear,
            ]);
            
            $this->bookingService->bookSlot($user, $slot);
        }
    }
} 