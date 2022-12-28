<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialistRating extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'specialist',
        'client',
        'booking',
        'ratings'
    ];
}
