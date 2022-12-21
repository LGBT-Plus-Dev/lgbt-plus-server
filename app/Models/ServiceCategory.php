<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'service', 'label', 'price', 'time_span'
    ];
}
