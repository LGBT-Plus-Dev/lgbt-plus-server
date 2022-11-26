<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use Illuminate\Http\Request;

class BarangayController extends Controller
{
    public function getList()
    {
        $results = Barangay::where('status', 1)->get();
        return $results;
    }
}
