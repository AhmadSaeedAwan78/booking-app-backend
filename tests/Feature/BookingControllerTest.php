<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
// use daatabase\factories\Service;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceDay;
use App\Models\User;

use Tests\TestCase;

class BookingControlerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetAvailableSlots() {
        // Create a service
        $service = Service::factory()->create();

        // Create some bookings for the service
        Booking::factory()->count(3)->create([
            'service_id' => $service->id,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $to = Carbon::today()->addDays(7)->format('Y-m-d'); 
        $from = Carbon::today()->format('Y-m-d');
        // Make a GET request to the getAvailableSlots endpoint
        $response = $this->get("/api/slots/all/{$service->id}?to={$to}&from={$from}");

        // Assert the response status code
        $response->assertStatus(200);

        // Assert the response structure
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'date',
                    'start_time',
                    'end_time',
                    'available_seats',
                ],
            ],
        ]);
    }

    public function testCreateBookings() {
        // Create a service
        $serviceDay = ServiceDay::factory()->create();

        // Create a user
        $user = User::factory()->create();

        // Make a POST request to the createBooking endpoint
        $response = $this->post('/api/slots/book/', [
            'serviceId' => $serviceDay->service_id,
            'date' => Carbon::today()->format('Y-m-d'),
            'startTime' => '10:00',
            'endTime' => '11:00',
            'people' => [
                [
                    'email' => $user->email,
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                ],
            ],
        ]);
        // dd($response);

        // Assert the response status code
        $response->assertStatus(200);

        // Assert the response message
        $response->assertJson([
            'message' => 'Booking created successfully',
        ]);

        // Assert the booking is created in the database
        $this->assertDatabaseHas('bookings', [
            'service_id' => $serviceDay->service_id,
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
    }

}

