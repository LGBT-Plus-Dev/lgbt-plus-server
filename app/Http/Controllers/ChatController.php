<?php

namespace App\Http\Controllers;

use App\Events\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function send(Request $req)
    {
        event(new Chat($req->user, $req->message));
        return [];
    }
}
