<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $table = 'services';
    protected $fillable = ['name', 'no_of_seats', 'clean_time', 'slot_duration', 'allow_booking_before_days', 'business_administrator_id'];
    public $timestamps = ['created_at', 'updated_at'];

    public function days() {
        return $this->hasMany(ServiceDay::class, 'service_id', 'id');
    }

    public function holidays() {
        return $this->hasMany(ServiceHoliday::class, 'service_id', 'id');
    }

    public function breaks() {
        return $this->hasMany(ServiceBreak::class, 'service_id', 'id');
    }

    public function bookings() {
        return $this->hasMany(Booking::class, 'service_id', 'id');
    }

    public function getOpeningHours($date) {
        // Retrieve the opening hours for the service
        $openingHours = $this->days()
            ->where('day', $date->englishDayOfWeek)
            ->first();
        return $openingHours ? $openingHours->only(['start_time', 'end_time']) : null;
    }

    public function isWithinBreak($time) {
        
        // Convert the time to a Carbon instance
        $time = Carbon::parse($time);

        // Get the breaks for the service
        $breaks = $this->breaks;

        // Check if the time falls within any of the breaks
        foreach ($breaks as $break) {
            $start = Carbon::parse($break->start_time);
            $end = Carbon::parse($break->end_time);
            if ($time->between($start, $end)) {
                return true;
            }
        }

        return false;
    }

   public function isSlotAvailable($date, $startTime, $endTime, $requiredBookings = 1){
        // Check if the requested time slot is within the opening hours and not in a break
        if (!$this->isWithinOpeningHours($startTime, $endTime, $this->getOpeningHours($date))) {
            return false;
        }
        $startTimeString = $startTime->format('H:i');
        $endTimeString = $endTime->format('H:i');
        // Retrieve existing bookings for the given date
        $existingBookings = $this->bookings()->where('service_id', $this->id)
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
        $availableSeats = $this->no_of_seats;
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
    }

    public function isPublicHoliday($date) {
        // Check if the given date falls within the range of any public holiday in the service_holidays table
        return  $this->holidays()
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();
    }

    function isWithinOpeningHours($startTime, $endTime, $openingHours) {
        $startOpeningHour = Carbon::parse($openingHours['start_time']);
        $endOpeningHour = Carbon::parse($openingHours['end_time']);
        return $startTime >= $startOpeningHour && $endTime <= $endOpeningHour;
    }
}
