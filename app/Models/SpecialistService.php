<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialistService extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'specialist',
        'service'
    ];

    protected $hidden = ['password'];
}
