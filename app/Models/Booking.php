<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client',
        'specialist',
        'service',
        'service_name',
        'category',
        'price',
        'zone',
        'date',
        'start_time',
        'end_time',
        'type',
        'status'
    ];
}
