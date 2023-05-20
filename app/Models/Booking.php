<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    protected $fillable = ['start_time', 'end_time', 'date', 'service_id', 'user_id'];  
    public $timestamps = ['created_at', 'updated_at'];

    public function user() {
        return $this->beloingsTo(User::class, 'user_id', 'id');
    }

    public function service() {
        return $this->beloingsTo(Service::class, 'service_id', 'id');
    }
}
