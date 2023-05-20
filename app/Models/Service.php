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

    public function isSlotAvailable($time) {
        // Convert the time to a Carbon instance
        $time = Carbon::parse($time);
        // Get the bookings for the service on the given date
        $bookings = $this->bookings()
                ->where(function ($query) use ($time) {
                    $query->where(function ($query) use ($time) {
                        $query->where('start_time', '<=', $time)
                            ->where('end_time', '>', $time);
                    })->orWhere(function ($query) use ($time) {
                        $query->where('start_time', '>=', $time)
                            ->where('start_time', '<', $time->copy()->addMinutes($this->duration));
                    });
                })->get();
        // Get the maximum number of clients per slot for the service
        $maxClientsPerSlot = $this->no_of_seats;

        // Check if the time slot is booked out
        if ($bookings->count() >= $maxClientsPerSlot) {
            return false;
        }

        // Check if the time slot falls within breaks or overlapping with existing bookings
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);

            if ($time->between($start, $end) || $start->between($time, $time->copy()->addMinutes($this->duration))) {
                return false;
            }
        }

        return true;
    }

    public function isPublicHoliday($date) {
        // Check if the given date falls within the range of any public holiday in the service_holidays table
        return  $this->holidays()
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();
    }

}
