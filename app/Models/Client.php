<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
        'fname',
        'lname',
        'mobile_no',
        'barangay',
        'zone',
        'address',
        'image_uri',
        'status'
    ];

    protected $hidden = ['password'];
}
