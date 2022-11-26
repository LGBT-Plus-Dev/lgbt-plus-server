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

        foreach($results as $item)
        {
            $item->categories = $this->getCategories($item->id);
        }

        return $results;
    }

    private function getCategories($id)
    {
        $results = ServiceCategory::where('service', $id)->get();
        return $results;
    }

    public function getById($id)
    {
        $item = Service::where('id', $id)->first();
        $item->categories = $this->getCategories($item->id);
        return $item;
    }

    public function create (Request $req)
    {
        $categories = $req->service['categories'];
        $service = $req->service;
        array_pop($service);

        $item = new Service($service);

        if($item->save())
        {
            $this->createCategories($categories, $item->id);
            return $this->getById($item->id);
        }
        else
            return 0;
    }

    public function update (Request $req, $id)
    {
        $categories = $req->service['categories'];
        $service = $req->service;
        array_pop($service);

        $item = Service::where('id', $id)->first();

        if(!$item) return 0;

        if($service) {
            $this->deleteCategories($id);
            $this->createCategories($categories, $id);
        }

        if($item->update($service))
            return $this->getById($item->id);
        else
            return 0;
    }

    private function createCategories ($categories, $service) 
    {
        foreach($categories as $category)
        {
            $item = new ServiceCategory($category);
            $item->service = $service;
            $item->save();
        }
    }

    private function deleteCategories ($service) 
    {
        ServiceCategory::where('service', $service)->delete();
    }

    public function delete ($id)
    {
        $this->deleteCategories($id);
        return Service::where('id', $id)->delete();
    }
}
