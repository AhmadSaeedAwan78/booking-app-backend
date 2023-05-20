# Laravel - booking app
## Setup

- `composer install`
- `add DB_NAME in .env`
- `php artisan serve`
- `php artisan migrate`
- `php artisan db:seed`

# Endpoints

 - GET `/api/slots/all/{serviceId}?from="Y-m-d"&to="Y-m-d"`
 
 - POST `/api/slots/book`
  >>payload: 
    {
        'serviceId' => 'required',
        'date' => 'required|format:Y-m-d',
        'startTime' => 'required|format:H:i',
        'endTime':  'required|format:H:i,
        'people':  [
            {
                'email',
                'firstName',
                'lastName',
            }
        ]
    }