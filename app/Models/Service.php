<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name', 'category', 'price', 'time_span', 'description'
    ];
}
