<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingControlerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetAvailableSlotsWithoutQuery()
    {
        // Service ID exists
        $serviceId = 1;

        $response = $this->get("/api/slots/all/{$serviceId}");

        $response->assertStatus(422);
        

        $day = Carbon::now();
        $serviceId = 1;
        $day = $day->format("Y-m-d");

        $response = $this->get("/api/slots/all/{$serviceId}?to={$day}&from={$day}");

        $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'startTime',
                    'endTime',
                    'day',
                    'date',
                    'availableSeats',
                ],
            ],
        ]);

    }

    public function testCreateBooking()
    {
        // Assuming you have valid booking data available
        // curr_date
        $bookingData = [
            'serviceId' => 1,
            'date' => '2023-05-20',
            'startTime' => '13:30',
            'endTime' => '13:40',
            'people' => [
                [
                    'email' => 'john@example.com',
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                ],
            ],
        ];

        $response = $this->post('/api/slots/book', $bookingData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Booking created successfully',
            ]);
    }

    // public function testGetAvailableSlots()
    // {
    //     // Create a service
    //     $service = Service::factory()->create();

    //     // Create some bookings for the service
    //     Booking::factory()->count(3)->create([
    //         'service_id' => $service->id,
    //         'date' => Carbon::today()->format('Y-m-d'),
    //     ]);

    //     // Make a GET request to the getAvailableSlots endpoint
    //     $response = $this->get('/api/available-slots/' . $service->id, [
    //         'from' => Carbon::today()->format('Y-m-d'),
    //         'to' => Carbon::today()->addDays(7)->format('Y-m-d'),
    //     ]);

    //     // Assert the response status code
    //     $response->assertStatus(200);

    //     // Assert the response structure
    //     $response->assertJsonStructure([
    //         'data' => [
    //             '*' => [
    //                 'date',
    //                 'start_time',
    //                 'end_time',
    //                 'available_seats',
    //             ],
    //         ],
    //     ]);

    //     // Assert the number of available slots
    //     $response->assertJsonCount(4, 'data');
    // }

    // public function testCreateBookings()
    // {
    //     // Create a service
    //     $service = Service::factory()->create();

    //     // Create a user
    //     $user = User::factory()->create();

    //     // Make a POST request to the createBooking endpoint
    //     $response = $this->post('/api/bookings', [
    //         'serviceId' => $service->id,
    //         'date' => Carbon::today()->format('Y-m-d'),
    //         'startTime' => '10:00',
    //         'endTime' => '11:00',
    //         'people' => [
    //             $user->id,
    //         ],
    //     ]);

    //     // Assert the response status code
    //     $response->assertStatus(200);

    //     // Assert the response message
    //     $response->assertJson([
    //         'message' => 'Booking created successfully',
    //     ]);

    //     // Assert the booking is created in the database
    //     $this->assertDatabaseHas('bookings', [
    //         'service_id' => $service->id,
    //         'user_id' => $user->id,
    //         'date' => Carbon::today()->format('Y-m-d'),
    //         'start_time' => '10:00',
    //         'end_time' => '11:00',
    //     ]);
    // }

}

