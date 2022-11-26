<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialistBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'specialist',
        'booking',
        'status'
    ];
}
