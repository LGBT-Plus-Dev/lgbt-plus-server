<?php

namespace App\Http\Controllers;

use App\Events\BookingEvent;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Specialist;
use App\Models\SpecialistBooking;
use App\Models\SpecialistLog;
use App\Models\SpecialistRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function getList()
    {
        $results = Booking::get();
        foreach($results as $item) {
            $specialist = Specialist::where('id', $item->specialist)->first();
            $client = Client::where('id', $item->client)->first();
            $payments = Payment::where('booking', $item->id)->get();

            $item->specialist = $specialist;
            $item->client = $client;
            $item->payments = $payments;
        }
        return $results;
    }

    public function getById ($id) 
    {
        $item = Booking::where('id', $id)->first();

        $specialist = Specialist::where('id', $item->specialist)->first();
        $client = Client::where('id', $item->client)->first();
        $payments = Payment::where('booking', $item->id)->get();

        $item->specialist = $specialist;
        $item->client = $client;
        $item->payments = $payments;

        return $item;
    }

    public function getGroupedBookings () {
        $results = Booking::orderBy('start_time')->get();

        foreach($results as $item) {
            $specialist = Specialist::where('id', $item->specialist)->first();
            $client = Client::where('id', $item->client)->first();
            $payments = Payment::where('booking', $item->id)->get();

            $item->specialist = $specialist;
            $item->client = $client;
            $item->payments = $payments;
        }
        
        return $results->groupBy('date');
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
          WHERE bookings.date='$today' AND bookings.status <> 'cancelled' AND bookings.status <> 'no_specialist'
          ORDER BY start_time"
        );

        return $results;

    }

    public function getTodaysBookingBySpecialist ($specialist) 
    {
        $today = date('Y-m-d');
        
        $results = Booking::where([
            ['specialist', $specialist],
            ['date', $today],
            ['status', '<>', 'cancelled'],
            ['status', '<>', 'no_specialist']
        ])->get();

        foreach($results as $item)
        {
            $specialist = Specialist::where('id', $item->specialist)->first();
            $client = Client::where('id', $item->client)->first();
            $payments = Payment::where('booking', $item->id)->get();

            $item->specialist = $specialist;
            $item->client = $client;
            $item->payments = $payments;
        }

        return $results;

    }

    public function getTodaysBookingByClient ($client) 
    {
        $today = date('Y-m-d');
        
        $results = Booking::where([
            ['client', $client],
            ['date', $today],
            ['status', '<>', 'cancelled'],
            ['status', '<>', 'no_specialist']
        ])->get();

        foreach($results as $item)
        {
            $specialist = Specialist::where('id', $item->specialist)->first();
            $client = Client::where('id', $item->client)->first();
            $payments = Payment::where('booking', $item->id)->get();

            $item->specialist = $specialist;
            $item->client = $client;
            $item->payments = $payments;
        }

        return $results;

    }


    public function getSpecialistBookings ($specialist)
    {
        $results = Booking::where([
            ['specialist', $specialist]
        ])->get();
        
        foreach($results as $item)
        {
            $specialist = Specialist::where('id', $item->specialist)->first();
            $client = Client::where('id', $item->client)->first();
            $payments = Payment::where('booking', $item->id)->get();

            $item->specialist = $specialist;
            $item->client = $client;
            $item->payments = $payments;
        }

        return $results;

    }

    public function getDeclinedBookings ($specialist)
    {
        $results = SpecialistBooking::where([
            ['specialist', $specialist],
            ['status', 'declined']
        ])->get();

        foreach($results as $item)
        {
            $booking = Booking::where('id', $item->booking)->first();
            $item->booking = $booking;
        }

        return $results;

    }

    public function getClientBookings ($client)
    {
        $results = Booking::where([
            ['client', $client]
        ])->get();

        foreach($results as $item)
        {
            $specialist = Specialist::where('id', $item->specialist)->first();
            $client = Client::where('id', $item->client)->first();
            $payments = Payment::where('booking', $item->id)->get();

            $item->specialist = $specialist;
            $item->client = $client;
            $item->payments = $payments;
        }

        return $results;
    }

    public function acceptBooking (Request $req)
    {
        $item = Booking::where('id', $req->booking)->first();

        //TODO: Add quota when accepted

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

        
        Log::channel('booking')->info("Booking@create", [$item]);

        if($booking['date'] === date('y-m-d')) {
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
        }
        else {
            $item->reason = "other date";
        }
        
        return $item;
    }

    public function addPayment (Request $req)
    {
        
        $payment = json_decode($req->payment);
        $item = new Payment();
        $item->amount = $payment->amount;
        $item->client = $payment->client;
        $item->specialist = $payment->specialist;
        $item->booking = $payment->booking;
        $item->save();

        $booking = Booking::where('id', $payment->booking)->first();

        if($req->hasFile('image')) {
            $original_filename = $req->file('image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $destination_path = 'storage/images/booking/payment/'.$booking->id.'/';
            $image = $booking->id . '_' . $item->id .'.' . $file_ext;

            if ($req->file('image')->move($destination_path, $image)) {
                $image_uri = $destination_path.$image;
                $item->update([
                    'image_uri' => $image_uri
                ]);
            } 
        }
        $booking->update([
            'status' => 'for_payment_confirmation'
        ]);

        $client = Client::where('id', $booking->client)->first();
        $specialist = Specialist::where('id', $booking->specialist)->first();

        event(new BookingEvent(
            $booking, 
            $specialist,
            $client,
            "payment", 
            "specialist"
        ));

        return $item;
    }

    public function confirmPayment ($booking) {

        $item = Booking::where('id', $booking)->first();

        $item->update([
            'status' => 'payment_accepted'
        ]);

        return $item;
    }

    public function completeBooking (Request $req) {

        $rating = $req->rating;

        $specialistRating = new SpecialistRating($rating);
        $specialistRating->save();
        
        $item = Booking::where('id', $specialistRating->booking)->first();
        
        $item->update([
            'status' => 'completed'
        ]);

        return $item;
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
        
        $specialists = SpecialistLog::select(
            DB::raw('specialists.*, specialist_logs.quota')
        )
        ->join('specialists','specialists.id','specialist_logs.specialist')
        ->where([
            ['specialists.status', 1],
            ['zone', $booking->zone],
            ['specialists.category', $booking->category],
            ['specialist_logs.time_out', null]
        ])
        ->orderBy('specialist_logs.quota')
        ->orderBy('specialist_logs.time_in')
        ->get();

        // Check Specialist Bookings
        foreach($specialists as $specialist)
        {
            if($booking)
            {
                $history = $this->checkHistory($booking->id, $specialist->id);
                if(!$history) continue;
            }

            $res = SpecialistBooking::select(
                DB::raw('bookings.*')
            )->join('bookings', 'bookings.id', 'specialist_bookings.booking')
            ->where([
                ['specialist_bookings.specialist', $specialist->id],
                ['bookings.date', $booking->date],
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
                    
                    if(!$lapsed) {
                        $noLapse = false;
                        break;
                    }
                }

                if($noLapse) {
                    $available = [...$available, $specialist];
                }
                
            }  
        }

        Log::channel('booking')->info("Booking@searchSpecialist", [$available]);

        if(count($available) > 0) {
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
