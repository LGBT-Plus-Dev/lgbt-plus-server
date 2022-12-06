<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use Illuminate\Http\Request;

class BarangayController extends Controller
{
    public function getList()
    {
        $results = Barangay::get();
        return $results;
    }

    public function getById($id)
    {
        $item = Barangay::where('id', $id)->first();
        return $item;
    }
    
    public function create (Request $req)
    {
        $barangay = $req->barangay;

        $item = new Barangay($barangay);

        if($item->save())
            return $this->getById($item->id);
        else
            return 0;
    }

    public function update (Request $req, $id)
    {
        $barangay = $req->barangay;

        $item = Barangay::where('id', $id)->first();

        if($item->update($barangay))
            return $this->getById($item->id);
        else
            return 0;
    }
}
