<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;


class CreateBookingRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize()
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'serviceId' => 'required|exists:services,id',
            'date' => 'required|date_format:Y-m-d|before_or_equal:'.Carbon::now()->addDays('7')->format('Y-m-d').'|after:'.Carbon::now()->format('Y-m-d'),
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',
            'people' => 'required|array|min:1',
            'people.*.email' => 'required|email',
            'people.*.firstName' => 'required',
            'people.*.lastName' => 'required',
        ];
    }

    public function messages() {
        return [
            'service_id.required' => 'Service is mendatory for booking.',
            'service_id.exists' => 'Invalid service.',
            'date.before_or_equal' => 'Invalid date given.',
        ];
    }
}
