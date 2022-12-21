<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialistLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'specialist',
        'date',
        'time_in',
        'time_out',
        'quota'
    ];
}
