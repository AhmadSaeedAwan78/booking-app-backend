<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\GetAvailableSlotsRequest;
use App\Http\Resources\AvailableSlotsResource;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function getAvailableSlots($serviceId, GetAvailableSlotsRequest $request) {
       try {
        $fromDate = Carbon::parse($request->input('from'));
        $toDate = Carbon::parse($request->input('to'));
        // Get the service
        $service = Service::findOrFail($serviceId);

        $slots = [];

        // Iterate over the date range
        for ($date = $fromDate; $date <= $toDate; $date->addDay()) {
            // Calculate the slots based on the opening hours, break duration, and slot duration
            $date = $date->startOfDay();

            // Retrieve the opening hours for the service
            $openingHours = $service->getOpeningHours($date);

            if (!$openingHours) {
                continue; // Skip to the next date if opening hours are not available
            }

            $startTime = Carbon::parse($openingHours['start_time']);
            $endTime = Carbon::parse($openingHours['end_time']);
            $slotDuration = $service->slot_duration + $service->clean_time;

            for ($time = $startTime; $time < $endTime; $time = $time->addMinutes($slotDuration)) {
                // Skip if the time is in the past
                if ($time->isPast()) {
                    continue;
                }

                $checkIsInBreak = $service->isWithinBreak($time);
                if ($checkIsInBreak) {
                    continue;
                }

                $existingBookingsCount = Booking::where('service_id', $service->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->where('start_time', $time->format('H:i'))
                    ->count();

                $availableSeats = $service->no_of_seats - $existingBookingsCount;

                if ($availableSeats > 0) {
                    $slot = new \stdClass(); // Create a new stdClass object
                    $slot->date = $date->format('Y-m-d');
                    $slot->start_time = $time->format('H:i');
                    $slot->end_time = $time->copy()->addMinutes($service->slot_duration)->format('H:i');
                    $slot->available_seats = $availableSeats;
                    $slots[] = $slot;
                }
            }
        }

        return AvailableSlotsResource::collection($slots);
       } catch (\Exception $e) {
          return response()->json(['message' => 'something went wrong'], 500);
       }
    }

    protected function isSlotAvailable($service, $date, $startTime, $endTime, $requiredBookings = 1){
        // Check if the requested time slot is within the opening hours and not in a break
      try {
        if (!$this->isWithinOpeningHours($startTime, $endTime, $service->getOpeningHours($date))) {
            return false;
        }
        $startTimeString = $startTime->format('H:i');
        $endTimeString = $endTime->format('H:i');
        // Retrieve existing bookings for the given date
        $existingBookings = Booking::where('service_id', $service->id)
            ->whereDate('date', $date)
            ->where(function ($query) use ($startTimeString, $endTimeString) {
                $query->where(function ($query) use ($startTimeString, $endTimeString) {
                    $query->where('start_time', '<', $endTimeString)
                        ->where('end_time', '>', $startTimeString);
                })->orWhere(function ($query) use ($startTimeString, $endTimeString) {
                    $query->where('start_time', '>=', $startTimeString)
                        ->where('start_time', '<', $endTimeString);
                })->orWhere(function ($query) use ($startTimeString, $endTimeString) {
                    $query->where('end_time', '>', $startTimeString)
                        ->where('end_time', '<=', $endTimeString);
                });
            })->get();

        // Check if the requested time slot overlaps with any existing bookings
        $availableSeats = $service->no_of_seats;
        foreach ($existingBookings as $booking) {
            $bookingStartTime = Carbon::parse($booking->start_time);
            $bookingEndTime = Carbon::parse($booking->end_time);

            if ($startTime < $bookingEndTime && $endTime > $bookingStartTime) {
                // Slot overlaps with an existing booking
                $availableSeats--;
            }
        }
        // Remaining Slots
        return $availableSeats >= $requiredBookings;
      } catch (\Exception $e) {
        return response()->json(['message' => 'something went wrong'], 500);
     }
    }

    public function createBooking(CreateBookingRequest $request) {
        $serviceId = $request->input('serviceId');
        $date = Carbon::parse($request->input('date'))->startOfDay();
        $startTime = Carbon::parse($request->input('startTime'));
        $endTime = Carbon::parse($request->input('endTime'));
        $people = $request->input('people');
        
        // Get the service
        $service = Service::findOrFail($serviceId);

        if($service->isPublicHoliday($date)) {
            return response()->json(['message' => 'Can not book slot on pubkic holiday'], 400);
        }
        
        if($endTime->diffInMinutes($startTime) != $service->slot_duration) {
            return response()->json(['message' => 'Invalid slot, duration must be atleast '. $service->slot_duration. ' minutes'], 400);
        }
        // Retrieve the opening hours for the service
        $openingHours = $service->getOpeningHours($date);

        if (!$openingHours) {
            return response()->json(['message' => 'Opening hours are not available for the given date'], 400);
        }

        $slotDuration = $service->slot_duration + $service->clean_time;

        // Check if the requested time slot is within the opening hours and not in a break
        if (!$this->isWithinOpeningHours($startTime, $endTime, $openingHours)) {
            return response()->json(['message' => 'The requested time slot is not within the opening hours'], 400);
        }

        if ($this->isWithinBreak($startTime, $service)) {
            return response()->json(['message' => 'The requested time slot is within a break'], 400);
        }

        // Check if the requested time slot is available
        if (!$this->isSlotAvailable($service, $date, $startTime, $endTime, count($people))) {
            return response()->json(['message' => 'The requested time slot is not available'], 400);
        }
        $bookingData = [];
        foreach($people as $person) {
            
            $user = new User();
            $user = $user->getUser($person);

            // Perform booking creation
            foreach ($people as $person) {
                $bookingData[] = [
                    'service_id' => $service->id,
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                ];
            }
        }
        Booking::insert($bookingData);

        return response()->json(['message' => 'Booking created successfully']);
    }

    private function isWithinOpeningHours($startTime, $endTime, $openingHours) {
        $startOpeningHour = Carbon::parse($openingHours['start_time']);
        $endOpeningHour = Carbon::parse($openingHours['end_time']);
        return $startTime >= $startOpeningHour && $endTime <= $endOpeningHour;
    }

    private function isWithinBreak($time, $service) {
        return $service->isWithinBreak($time);
    }
}
