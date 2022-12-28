<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use App\Models\SpecialistLog;
use App\Models\SpecialistRating;
use App\Models\SpecialistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialistController extends Controller
{
    public function getList()
    {
        $results = Specialist::where('status', 1)->get();

        foreach($results as $item) 
        {
            $ratings = SpecialistRating::select(
                    DB::raw("AVG(ratings) as average, SUM(ratings) as total")
                )
                ->where('specialist', $item->id)
                ->first();

            $item->ratings_total = $ratings->total ? $ratings->total : 0;
            $item->ratings_average = $ratings->average ? $ratings->average : 0;
        }

        return $results;
    }

    public function getById($id)
    {
        $item = Specialist::where('id', $id)->first();

        $ratings = SpecialistRating::select(
                DB::raw("AVG(ratings) as average, SUM(ratings) as total")
            )
            ->where('specialist', $item->id)
            ->first();

        $item->ratings_total = $ratings->total ? $ratings->total : 0;
        $item->ratings_average = $ratings->average ? $ratings->average : 0;

        return $item;
    }
    
    public function getAttendance ($date)
    {
        $specialists = SpecialistLog::select(
            DB::raw('specialists.*, specialist_logs.quota, specialist_logs.time_in, specialist_logs.time_out')
        )
        ->join('specialists','specialists.id','specialist_logs.specialist')
        ->where([
            ['specialist_logs.date', $date]
        ])
        ->orderBy('specialist_logs.time_in')
        ->get();

        return $specialists;
    }

    public function getSpecialistAttendanceToday ($specialist)
    {
        $currentDate = date("Y-m-d");
        $item = SpecialistLog::select(
            DB::raw('specialists.*, specialist_logs.quota, specialist_logs.time_in')
        )
        ->join('specialists','specialists.id','specialist_logs.specialist')
        ->where([
            ['specialists.id', $specialist],
            ['specialist_logs.date', $currentDate]
        ])
        ->first();

        return $item;
    }

    public function getAttendanceFrom ($date)
    {
        $currentDate = date("Y-m-d");

        $specialists = SpecialistLog::select(
            DB::raw('specialists.*, specialist_logs.quota, specialist_logs.date, specialist_logs.time_in, specialist_logs.time_out')
        )
        ->join('specialists','specialists.id','specialist_logs.specialist')
        ->whereBetween('specialist_logs.date', [$date, $currentDate])
        ->orderBy('specialist_logs.date', 'DESC')
        ->orderBy('specialist_logs.time_in')
        ->get();

        return $specialists;
    }

    public function timeIn ($specialist)
    {
        
        $currentDate = date("Y-m-d");
        $currentTime = date("H:i:s");

        $log = new SpecialistLog();
        $log->specialist = $specialist;
        $log->date = $currentDate;
        $log->time_in = $currentTime;
        $log->quota = 0;

        if($log->save()) {
            return $log;
        }
        else {
            return 0;
        }
    }

    public function timeOut ($specialist) 
    {
        $currentDate = date("Y-m-d");
        $currentTime = date("H:i:s");

        $log = SpecialistLog::where([
            ['date', $currentDate],
            ['specialist', $specialist]
        ])
        ->first();

        if($log->update([
            'time_out' => $currentTime
        ])) {
            return $log;
        }
        else {
            return 0;
        }
    }

    public function getSpecialistFifo($category)
    {
        $currentDate = date("Y-m-d");
        $specialists = SpecialistLog::select(
            DB::raw('specialists.*, specialist_logs.quota, specialist_logs.time_in')
        )
        ->join('specialists','specialists.id','specialist_logs.specialist')
        ->where([
            ['specialists.status', 1],
            ['specialists.category', $category],
            ['specialist_logs.time_out', null],
            ['specialist_logs.date', $currentDate]
        ])
        ->orderBy('specialist_logs.quota')
        ->orderBy('specialist_logs.time_in')
        ->get();

        return $specialists;
    }

    public function authenticate(Request $req)
    {
        $user = Specialist::where([
            ['username',$req->username],
            ['password', $req->password]
        ])->first();

        return $user;
    }

    public function create (Request $req)
    {
        $specialist = $req->specialist;
        
        $count = Specialist::where('category', $specialist['category'])->get()->count();
        
        if($count >= 10) {
            return "Maximum count of specialist for the selected category has been reached!";
        }

        $item = new Specialist($specialist);

        if($item->save())
        {
            return $this->getById($item->id);
        }
        else
            return "Something went wrong!";
    }

    public function update (Request $req, $id)
    {
        $specialist = $req->specialist;

        $item = Specialist::where('id', $id)->first();

        if($item->category !== $specialist['category']) {
            $count = Specialist::where('category', $specialist['category'])->get()->count();
        
            if($count >= 10) {
                return "Maximum count of specialist for the selected category has been reached!";
            }
        }


        if(!$item) return 0;

        if($item->update($specialist))
            return $this->getById($item->id);
        else
            return 0;
    }

    public function delete ($id)
    {
        try {
            return Specialist::where('id', $id)->delete();
        } catch (\Throwable $th) {
            return Specialist::where('id', $id)->update([
                'status' => 0
            ]);
        }
    }
}
