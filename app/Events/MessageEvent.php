<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $messages;

    public function __construct($booking, $messages)
    {
        $this->booking = $booking;
        $this->messages = $messages;
    }

    public function broadcastOn()
    {
        $channel = 'chat_'.strval($this->booking['id']);
        return [$channel];
    }

    public function broadcastAs()
    {
        return 'messages';
    }
}
