<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function getList()
    {
        $results = Service::get();
        return $results;
    }

    public function getById($id)
    {
        $item = Service::where('id', $id)->first();
        return $item;
    }

    public function create (Request $req)
    {
        $service = $req->service;

        $item = new Service($service);

        if($item->save())
            return $this->getById($item->id);
        else
            return 0;
    }

    public function update (Request $req, $id)
    {
        $service = $req->service;

        $item = Service::where('id', $id)->first();

        if($item->update($service))
            return $this->getById($item->id);
        else
            return 0;
    }

    public function delete ($id)
    {
        return Service::where('id', $id)->delete();
    }
}
