<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialist extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
        'fname',
        'lname',
        'email',
        'mobile_no',
        'address',
        'image_uri',
        'status'
    ];
}
