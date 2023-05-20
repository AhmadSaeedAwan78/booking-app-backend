<?php
namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceDay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceDayFactory extends Factory
{
    protected $model = ServiceDay::class;

    public function definition()
    {
        $service = Service::factory()->create();
        return [
            'day' => "Saturday",
            'start_time' => "08:00",
            'end_time' => "22:00",
            'service_id' => $service->id,
        ];
    }
}
