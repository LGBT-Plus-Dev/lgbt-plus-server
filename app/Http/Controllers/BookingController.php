<?php

namespace App\Http\Controllers;

use App\Events\BookingEvent;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Specialist;
use App\Models\SpecialistBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function getList()
    {
        $results = Booking::where('status', 1)->get();
        return $results;
    }

    public function getById () 
    {

    }
    
    public function getTodaysBooking () 
    {
        $today = date('Y-m-d');
        
        $results = DB::select(
            "      SELECT bookings.*,
            clients.fname as client_fname,
            clients.lname as client_lname,
            specialists.fname as specialist_fname,
            specialists.lname as specialist_lname
          FROM bookings
          LEFT JOIN clients ON clients.id=bookings.client
          LEFT JOIN specialists ON specialists.id=bookings.specialist
          WHERE bookings.date='$today'
          ORDER BY start_time"
        );

        return $results;

    }

    public function getSpecialistBookings (Request $req)
    {
        $bookings = Booking::where([
            ['specialist', $req->specialist],
            ['status', $req->status]
        ])->get();

    }

    public function getSpecialistDeclinedBookings (Request $req)
    {
        $results = SpecialistBooking::where([
            ['specialist', $req->specialist],
            ['status', 'declined']
        ])->get();

        foreach($results as $item)
        {
            $booking = Booking::where('id', $item->booking)->first();
            $item->booking = $booking;
        }

        return $results;

    }

    public function acceptBooking (Request $req)
    {
        $item = Booking::where('id', $req->booking)->first();

        $history = SpecialistBooking::where([
            ['booking', $req->booking],
            ['specialist', $req->specialist]
        ])->first();

        $history->update([
            "status" => "accepted"
        ]);

        $item->update([
            "status" => "waiting_for_payment"
        ]);

        $specialist = Specialist::where('id', $item->specialist)->first();
        $client = Client::where('id', $item->client)->first();

        event(new BookingEvent(
            $item, 
            $specialist,
            $client,
            "accepted", 
            "client")
        );

        return $item;
    }

    public function declineBooking (Request $req)
    {
        $item = Booking::where('id', $req->booking)->first();

        $history = SpecialistBooking::where([
            ['booking', $req->booking],
            ['specialist', $req->specialist]
        ])->first();

        $history->update([
            "status" => "declined"
        ]);

        $item->update([
            "status" => "pending"
        ]);

        
        $specialist = $this->searchSpecialist($item);
        $client = Client::where('id', $item->client)->first();

        if($specialist['selected'])
        {
            $item->specialist = $specialist['selected']->id;
            $item->update([
                'status' => 'for_acceptance',
                'specialist' => $specialist['selected']->id
            ]);

            $pending = new SpecialistBooking();
            $pending->specialist = $specialist['selected']->id;
            $pending->booking = $item->id;
            $pending->status = "pending";
            $pending->save();

            // $client = Client::where('id', $item->client)->first();

            event(new BookingEvent(
                $item, 
                $specialist['selected'],
                $client,
                "new", 
                "specialist"
            ));
        }
        else
        {
            $item->update([
                'status' => 'no_specialist',
                'specialist' => null
            ]);
            $item->reason = $specialist['reason'];

            event(new BookingEvent(
                $item, 
                $specialist,
                $client,
                "no_specialist",
                "client")
            );
        }

        return $item;
    }

    public function create (Request $req) 
    {
        $booking = $req->booking;
        $item = new Booking($booking);
        $item->save();
        
        $specialist = $this->searchSpecialist($item);
        
        if($specialist['selected'])
        {
            $item->specialist = $specialist['selected']->id;
            $item->update([
                'status' => 'for_acceptance',
                'specialist' => $specialist['selected']->id
            ]);

            $pending = new SpecialistBooking();
            $pending->specialist = $specialist['selected']->id;
            $pending->booking = $item->id;
            $pending->status = "pending";
            $pending->save();

            $client = Client::where('id', $item->client)->first();

            event(new BookingEvent(
                $item, 
                $specialist['selected'],
                $client,
                "new", 
                "specialist")
            );
        }
        else
        {
            $item->update([
                'status' => 'no_specialist',
                'specialist' => null
            ]);
            $item->reason = $specialist['reason'];
        }

        return $item;
    }

    public function sendPayment (Request $req)
    {
        $item = Booking::where('id', $req->booking);
        $payment = new Payment($req->payment);
        $payment->save();

        $client = Client::where('id', $item->client)->first();
        $specialist = Specialist::where('id', $item->specialist)->first();

        event(new BookingEvent(
            $item, 
            $specialist,
            $client,
            "payment", 
            "specialist")
        );
    }

    private function getPayments ($booking) {
        $payments = Payment::where('booking', $booking)->get();
        return $payments;
    }

    private function searchSpecialist ($booking) 
    {
        $result = [
            "selected" => null,
            "reason" => ""
        ];
      
        $available = array();
      
        $startTime = $booking->start_time;
        $endTime = $booking->end_time;
        
        
        $specialists = Specialist::select(
            DB::raw('specialists.*')
        )->join('specialist_services','specialist_services.specialist','specialists.id')
        ->where([
            ['specialists.status', 1],
            ['specialist_services.service', $booking->service]
        ])->get();

        //Check Specialist Bookings
        foreach($specialists as $specialist)
        {
            if($booking)
            {
                $history = $this->checkHistory($booking->id, $specialist->id);
                if(!$history) continue;
            }

            $res = Booking::where([
                ['specialist', $specialist->id],
                ['date', $booking->date],
            ])->get();

            if(count($res) === 0) {
                $available = [...$available, $specialist];
            }
            else {
                $noLapse = true;
                foreach($res as $books)
                {
                    $lapsed = $this->checkBookingDate(
                        $booking->date,
                        $books->start_time,
                        $books->end_time,
                        $startTime,
                        $endTime
                    );
                    
                    if($lapsed) {
                        $noLapse = false;
                        break;
                    }
                }

                if($noLapse) {
                    $available = [...$available, $specialist];
                }
                
            }  
        }

        if(count($available) > 0) {
            // $result['selected'] = $this->getSpecialistByQuota($booking['date'], $available);
            $result['selected'] = $available[0];
        }
        else {
            if(count($specialists) > 0) {
                $result['reason'] = "No available specialist for selected schedule";
            }
            else {
                $result['reason'] = "No available specialist for selected service";
            }
        }
    
        return $result;
    }

    private function checkBookingDate ($date, $start1, $end1, $start2, $end2)
    {
        $a_start = date_parse_from_format("Y-m-d H:i:s", $date.' '.$start1);
        $b_start = date_parse_from_format("Y-m-d H:i:s", $date.' '.$start2);

        $a_end = date_parse_from_format("Y-m-d H:i:s", $date.' '.$end1);
        $b_end = date_parse_from_format("Y-m-d H:i:s", $date.' '.$end2);
        
        if ($a_start >= $b_start && $b_end > $a_start) return true;
        if ($b_start > $a_start && $b_start < $a_end ) return true;
    
        if ($a_end <= $b_end && $b_start < $a_end) return true;
        if ($b_end > $a_end && $b_end < $a_end) return true;
    
        if ($a_start < $b_start && $b_start < $a_end) return true;
        if ($a_start < $b_end   && $b_end  < $a_end) return true;
        if ($b_start <  $a_start && $a_end  <  $b_end) return true;

        return false;
    }

    private function checkHistory($booking, $specialist)
    {
        $history = SpecialistBooking::where([
            ['booking', $booking],
            ['specialist', $specialist]
        ])->get();

        if(count($history) > 0)
        {
            return false;
        }

        return true;
    }
}
