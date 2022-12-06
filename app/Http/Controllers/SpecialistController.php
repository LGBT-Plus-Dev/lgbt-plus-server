<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use App\Models\SpecialistLog;
use App\Models\SpecialistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialistController extends Controller
{
    public function getList()
    {
        $results = Specialist::where('status', 1)->get();

        // foreach($results as $item)
        //     $item->services = $this->getSpecialistServices($item->id);

        return $results;
    }

    public function getById($id)
    {
        $item = Specialist::where('id', $id)->first();
        // if($item)
        //     $item->services = $this->getSpecialistServices($id);
        return $item;
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

    public function timeIn (Request $req) {
        $log = new SpecialistLog($req->log);

        if($log->save()) {
            return $log;
        }
        else {
            return 0;
        }
    }

    public function timeOut (Request $req, $id) {
        $log = SpecialistLog::where('id', $id)->first();

        if($log->update($req->log)) {
            return $log;
        }
        else {
            return 0;
        }
    }

    public function getSpecialistByService($serviceId)
    {
        $results = Specialist::select(
            DB::raw('specialists.*')
        )
        ->join('specialist_services', 'specialist_services.specialist', 'specialists.id')
        -> where([
            ['specialists.status', 1],
            ['specialist_services.service', $serviceId]
        ])->get();

        return $results;
    }

    private function getSpecialistServices ($specialistId)
    {
        if($specialistId)
        {
            $services = SpecialistService::select(
                DB::raw('specialist_services.*', 'services.name')
            )
            ->join('services','specialist_services.service', 'services.id')
            ->where('specialist_services.specialist',$specialistId)
            ->get();

            return $services;
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
        //$services = $req->specialist['services'];
        $specialist = $req->specialist;
        //array_pop($specialist);

        $item = new Specialist($specialist);

        if($item->save())
        {
            //$this->createSpecialistServices($services, $item->id);
            return $this->getById($item->id);
        }
        else
            return 0;
    }

    public function update (Request $req, $id)
    {
        //$services = $req->specialist['services'];
        $specialist = $req->specialist;
        //array_pop($specialist);

        $item = Specialist::where('id', $id)->first();

        if(!$item) return 0;

        // if($services) {
        //     $this->deleteSpecialistServices($id);
        //     $this->createSpecialistServices($services, $id);
        // }

        if($item->update($specialist))
            return $this->getById($item->id);
        else
            return 0;
    }

    private function createSpecialistServices ($services, $specialist) 
    {
        foreach($services as $service)
        {
            $item = new SpecialistService();
            $item->specialist = $specialist;
            $item->service = $service;
            $item->save();
        }
    }

    private function deleteSpecialistServices ($specialist) 
    {
        SpecialistService::where('specialist',$specialist)->delete();
    }

    public function delete ($id)
    {
        //$this->deleteSpecialistServices($id);
        
        try {
            return Specialist::where('id', $id)->delete();
        } catch (\Throwable $th) {
            return Specialist::where('id', $id)->update([
                'status' => 0
            ]);
        }
    }
}
