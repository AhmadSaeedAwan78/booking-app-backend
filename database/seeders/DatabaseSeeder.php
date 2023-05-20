<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BussinessAdministrator;
use App\Models\Service;
use App\Models\ServiceBreak;
use App\Models\ServiceDay;
use App\Models\ServiceHoliday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\TryCatch;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */


    protected function ServicesSeeder() {
        User::firstORCreate([
            'first_name' => "Mike",
            'last_name' => "Tyson",
            'email' => "mike.".rand(000,999)."@example.com",
            'password' => Hash::make('Admin@123'),
        ]);

        $businessAdmin = BussinessAdministrator::firstOrCreate([
            'name' => 'John Dow',
            // 'created_at' => Carbon::now(),
            // 'updated_at' => Carbon::now(),
        ]);
        $menHairCutService =  Service::firstOrCreate([
            'name' => 'Men Haircut',
            'no_of_seats' => 3,
            'clean_time' => 5,
            'allow_booking_before_days' => 7,
            'slot_duration' => 10,
            'business_administrator_id' => $businessAdmin->id,
        ]);
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach($days as $key => $day) {
            if($day == 'Sunday') {
                 continue; 
            }
            if($day == 'Saturday') {
                $start_time = '10:00';
                $end_time = '22:00';
            } else {
                $start_time = '08:00';
                $end_time = '20:00';
            }
            ServiceDay::firstOrCreate([
                'day' => $day,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'service_id' => $menHairCutService->id,
            ]);
        }

        ServiceBreak::firstOrCreate([
            'name' => 'Lunch Break',
            'start_time' =>'12:00',
            'end_time' => '13:00',
            'service_id' => $menHairCutService->id,
        ]);

        ServiceBreak::firstOrCreate([
            'name' => 'Cleaning Break',
            'start_time' =>'15:00',
            'end_time' => '16:00',
            'service_id' => $menHairCutService->id,
        ]);
    
        $holiday = Carbon::now()->addDays(3)->format('Y-m-d');
        ServiceHoliday::firstOrCreate([
            'name' => 'Public Holiday',
            'start_date' => $holiday,
            'end_date' => $holiday,
            'service_id' => $menHairCutService->id,
        ]);
      $this->generateBookings($menHairCutService);

        
        // Woman Haircut
        $womanHairCutService = Service::firstOrCreate([
            'name' => 'Woman Haircut',
            'no_of_seats' => 3,
            'clean_time' => 10,
            'allow_booking_before_days' => 7,
            'slot_duration' => 60,
            'business_administrator_id' => $businessAdmin->id,
        ]);
        foreach($days as $key => $day) {
            if($day == 'Sunday') {
                 continue; 
            }

            if($day == 'Saturday') {
                $start_time = '10:00';
                $end_time = '22:00';
            } else {
                $start_time = '08:00';
                $end_time = '20:00';
            }

            ServiceDay::firstOrCreate([
                    'day' => $day,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'service_id' => $womanHairCutService->id,
            ]);
        }
        ServiceBreak::firstOrCreate([
            'name' => 'Lunch Break',
            'start_time' =>'12:00',
            'end_time' => '13:00',
            'service_id' => $womanHairCutService->id,
        ]);
    
        $holiday = Carbon::now()->addDays(3)->format('Y-m-d');
        ServiceHoliday::firstOrCreate([
            'name' => 'Public Holiday',
            'start_date' => $holiday,
            'end_date' => $holiday,
            'service_id' => $womanHairCutService->id,
        ]);
        $this->generateBookings($womanHairCutService);
    }
    protected function generateBookings($service)
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addDays(7);
        for ($date = $startDate; $date <= $endDate; $date = $date->addDay()) {
            if($service->isPublicHoliday($date)) {
                continue;
            }
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }
    
            $existingBookings = Booking::where('service_id', $service->id)
                ->where('date', $date->format('Y-m-d'))
                ->get();

            if ($existingBookings->isNotEmpty()) {
                $existingBookings = $existingBookings->groupBy('start_time');
            } else {
                $existingBookings = collect(); // Create an empty collection
            }

    
            $openingHour = $service->getOpeningHours($date);
            // if(!$openingHour) {continue;}
            if ($service->isWithinBreak($openingHour['start_time']) || $service->isWithinBreak($openingHour['end_time'])) {
                continue;
            }
    
            $startTime = Carbon::parse($openingHour['start_time']);
            $endTime = Carbon::parse($openingHour['end_time']);
            $slotDuration = $service->slot_duration + $service->clean_time;
    
            for ($time = $startTime; $time < $endTime; $time = $time->addMinutes($slotDuration)) {
                $checkIsInBreak = $service->isWithinBreak($time);
                if ($checkIsInBreak) {
                    continue;
                }

                // Check if the current time is in the past
                if ($time->isPast()) {
                    continue;
                }
                

                $existingBookingsCount = isset($existingBookings[$time->format('H:i')]) ? $existingBookings[$time->format('H:i')]->count() : 0 ?? 0;
                $availableSeats = $service->no_of_seats - $existingBookingsCount;
    
                if ($availableSeats > 0) {
                    $bookings = [];
                    for ($i = 0; $i < $availableSeats; $i++) {
                        $bookingData = [
                            'service_id' => $service->id,
                            'user_id' => 1,
                            'date' => $date->format('Y-m-d'),
                            'start_time' => $time->format('H:i'),
                            'end_time' => $time->copy()->addMinutes($service->slot_duration)->format('H:i'),
                        ];
    
                        $bookings[] = $bookingData;
                    }
    
                    Booking::insert($bookings);
                }
            }
        }
        // dd('finished', DB::table('bookings')->count());
    }

    public function run()
    {
        DB::transaction(function($table) {
            $this->ServicesSeeder();
        });
    }
}
