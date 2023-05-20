<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailableSlotsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) {
        return [
            'startTime' => $this->start_time,
            'endTime' => $this->end_time,
            'date' => $this->date,
            'availableSeats' => $this->available_seats,
        ];
    }
}
