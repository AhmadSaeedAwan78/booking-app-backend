<?php

namespace Tests\Feature;

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
    public function testGetAvailableSlots()
    {
        // Service ID exists
        $serviceId = 1;

        $response = $this->get("/api/slots/all/{$serviceId}");

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

        //Service ID does not exist
        $serviceId = 100;

        $response = $this->get("/api/slots//{$serviceId}");

        $response->assertStatus(404);
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
}
