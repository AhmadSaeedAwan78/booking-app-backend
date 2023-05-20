<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDay extends Model
{
    use HasFactory;
    protected $table = 'service_days';
    protected $fillable = ['day', 'start_time', 'end_time', 'service_id'];    
    public $timestamps = ['created_at', 'updated_at'];

    public function service() {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}
