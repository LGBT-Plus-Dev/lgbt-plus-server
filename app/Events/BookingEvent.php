<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $specialist;
    public $client;
    public $eventType;
    public $receiver;

    public function __construct($booking, $specialist,$client, $eventType, $receiver)
    {
        $this->booking = $booking;
        $this->specialist = $specialist;
        $this->client = $client;
        $this->eventType = $eventType;
        $this->receiver = $receiver;
    }

    public function broadcastOn()
    {
        $channel = 'booking';
        return [$channel];
    }

    public function broadcastAs()
    {
        return 'transaction';
    }
}
