<?php

namespace App\Services;

use App\Models\User;
use App\Models\TimeSlot;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingService
{
    private const MIN_HOURS_BEFORE_BOOKING = 12;
    private const SLOT_DURATION = 15; // minutes
    private const HOUR_DURATION = 60; // minutes

    public function getAvailableSlots(User $user, int $maxWeeks = 3): Collection
    {
        $now = Carbon::now();
        $minStartTime = $now->copy()->addHours(self::MIN_HOURS_BEFORE_BOOKING);
        
        $slots = collect();
        $currentWeek = $now->weekOfYear;
        
        for ($week = 0; $week < $maxWeeks; $week++) {
            $weekNumber = $currentWeek + $week;
            $weekSlots = $this->getSlotsForWeek($user, $weekNumber, $minStartTime);
            $slots = $slots->concat($weekSlots);
        }

        return $slots;
    }

    private function getSlotsForWeek(User $user, int $weekNumber, Carbon $minStartTime): Collection
    {
        $weekStart = Carbon::now()->setISODate(Carbon::now()->year, $weekNumber)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        // Get all booked slots for the week
        $bookedSlots = TimeSlot::where('user_id', $user->id)
            ->where('is_booked', true)
            ->whereBetween('start_time', [$weekStart, $weekEnd])
            ->get();

        // If no slots are booked, show all available slots
        if ($bookedSlots->isEmpty()) {
            return $this->generateAllSlotsForWeek($user, $weekNumber, $minStartTime);
        }

        // Get the first booked slot in the week
        $firstBookedSlot = $bookedSlots->sortBy('start_time')->first();
        $hourNumber = $firstBookedSlot->hour_number;

        // Get all slots in the same hour as the first booked slot
        $hourSlots = TimeSlot::where('user_id', $user->id)
            ->where('week_number', $weekNumber)
            ->where('hour_number', $hourNumber)
            ->where('is_booked', false)
            ->where('start_time', '>=', $minStartTime)
            ->get();

        // If we have less than 3 booked slots in this hour, show remaining slots
        $bookedSlotsInHour = $bookedSlots->where('hour_number', $hourNumber);
        if ($bookedSlotsInHour->count() < 3) {
            return $hourSlots;
        }

        // If hour is fully booked, show all available slots for the rest of the week
        return $this->generateAllSlotsForWeek($user, $weekNumber, $minStartTime)
            ->where('start_time', '>', $firstBookedSlot->end_time);
    }

    private function generateAllSlotsForWeek(User $user, int $weekNumber, Carbon $minStartTime): Collection
    {
        $weekStart = Carbon::now()->setISODate(Carbon::now()->year, $weekNumber)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $slots = collect();
        $currentTime = $minStartTime->copy();

        while ($currentTime < $weekEnd) {
            // Check if the hour is free
            if ($this->isHourFree($user, $currentTime)) {
                // Generate slots for this hour
                $hourSlots = $this->generateSlotsForHour($user, $currentTime, $weekNumber);
                $slots = $slots->concat($hourSlots);
            }
            
            $currentTime->addHour();
        }

        return $slots;
    }

    private function isHourFree(User $user, Carbon $time): bool
    {
        return !TimeSlot::where('user_id', $user->id)
            ->where('is_booked', true)
            ->where('start_time', '>=', $time)
            ->where('start_time', '<', $time->copy()->addHour())
            ->exists();
    }

    private function generateSlotsForHour(User $user, Carbon $hourStart, int $weekNumber): Collection
    {
        $slots = collect();
        $currentTime = $hourStart->copy();
        $hourEnd = $hourStart->copy()->addHour();

        while ($currentTime < $hourEnd) {
            $slot = TimeSlot::create([
                'user_id' => $user->id,
                'start_time' => $currentTime,
                'end_time' => $currentTime->copy()->addMinutes(self::SLOT_DURATION),
                'is_booked' => false,
                'week_number' => $weekNumber,
                'hour_number' => $hourStart->hour,
            ]);

            $slots->push($slot);
            $currentTime->addMinutes(self::SLOT_DURATION);
        }

        return $slots;
    }

    public function bookSlot(User $user, TimeSlot $slot): Booking
    {
        // Check if the slot is still available
        if ($slot->is_booked) {
            throw new \Exception('Slot is already booked');
        }

        // Check if user has reached their weekly booking limit
        if ($this->hasReachedWeeklyLimit($user, $slot->week_number)) {
            throw new \Exception('Weekly booking limit reached');
        }

        // Create the booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'time_slot_id' => $slot->id,
            'status' => 'confirmed',
            'booked_at' => now(),
        ]);

        // Mark the slot as booked
        $slot->update(['is_booked' => true]);

        return $booking;
    }

    private function hasReachedWeeklyLimit(User $user, int $weekNumber): bool
    {
        $weeklyBookings = Booking::whereHas('timeSlot', function ($query) use ($user, $weekNumber) {
            $query->where('user_id', $user->id)
                  ->where('week_number', $weekNumber);
        })->count();

        return $weeklyBookings >= $user->weekly_booking_limit;
    }
} 