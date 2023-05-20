<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class GetAvailableSlotsRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from' => 'required|date_format:Y-m-d', // Optional date parameter, should be in the "Y-m-d" format
            'to' => 'required|date_format:Y-m-d', // Optional date parameter, should be in the "Y-m-d" format
        ];
    }

    public function messages() {
        return [
            'date.date_format' => 'The date should be in the format "Y-m-d".',
        ];
    }
}
