<?php

namespace App\Http\Controllers;

use App\Events\Chat;
use App\Events\ChatEvent;
use App\Events\MessageEvent;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Message;
use App\Models\Specialist;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function send(Request $req)
    {
        $message = $req->message;
        $item = new Message($message);
        if($item->save()) {
            $booking = Booking::where('id', $message['booking'])->first();

            $client = Client::where('id', $message['client'])->first();
            $booking->client = $client;

            $specialist = Specialist::where('id', $message['specialist'])->first();
            $booking->specialist = $specialist;

            $messages = $this->getMessages($message['booking']);
            event(new ChatEvent($message));
            event(new MessageEvent($booking, $messages));
            return $item;
        }
        else
            return 0;
    }

    public function getMessages($booking)
    {
        $results = Message::where('booking', $booking)
            ->orderBy('created_at', 'desc')
            ->get();
        return $results;
    }
}
