<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BussinessAdministrator extends Model
{
    use HasFactory;
    protected $table = 'business_administrators';
    protected $fillable = ['name'];
    public $timestamps = ['created_at', 'updated_at'];

    public function services()
    {
        return $this->hasMany(ServiceType::class, 'business_administrator_id', 'id');
    }
}
