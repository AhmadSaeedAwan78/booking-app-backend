<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceHoliday extends Model
{
    use HasFactory;
    protected $table = 'service_holidays';
    protected $fillable = ['name', 'start_date', 'end_date', 'service_id'];    
    public $timestamps = ['created_at', 'updated_at'];

    public function service() {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}
