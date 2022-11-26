<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use App\Models\SpecialistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialistController extends Controller
{
    public function getList()
    {
        $results = Specialist::where('status', 1)->get();

        foreach($results as $item)
            $item->services = $this->getSpecialistServices($item->id);

        return $results;
    }

    public function getById($id)
    {
        $item = Specialist::where('id', $id)->first();
        if($item)
            $item->services = $this->getSpecialistServices($id);
        return $item;
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
        $services = $req->specialist['services'];
        $specialist = $req->specialist;
        array_pop($specialist);

        $item = new Specialist($specialist);

        if($item->save())
        {
            $this->createSpecialistServices($services, $item->id);
            return $this->getById($item->id);
        }
        else
            return 0;
    }

    public function update (Request $req, $id)
    {
        $services = $req->specialist['services'];
        $specialist = $req->specialist;
        array_pop($specialist);

        $item = Specialist::where('id', $id)->first();

        if(!$item) return 0;

        if($services) {
            $this->deleteSpecialistServices($id);
            $this->createSpecialistServices($services, $id);
        }

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
        $this->deleteSpecialistServices($id);
        
        try {
            return Specialist::where('id', $id)->delete();
        } catch (\Throwable $th) {
            return Specialist::where('id', $id)->update([
                'status' => 0
            ]);
        }
    }
}