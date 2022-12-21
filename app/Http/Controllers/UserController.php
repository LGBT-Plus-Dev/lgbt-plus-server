<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function authenticate(Request $req)
    {
        $user = User::where([
            ['username',$req->username],
            ['password', $req->password]
        ])->first();

        return $user;
    }
}
