<?php

namespace Database\Factories;

use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'slot_duration' => 60,
            'clean_time' => 15,
            'no_of_seats' => 10,
            'allow_booking_before_days' => 5,
            'business_administrator_id' => 1
        ];
    }
}
