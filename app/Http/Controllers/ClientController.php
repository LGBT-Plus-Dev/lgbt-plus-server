<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function getList()
    {
        $results = Client::where('status', 1)->get();
        return $results;
    }

    public function getById($id)
    {
        $item = Client::where('id', $id)->first();
        return $item;
    }

    public function authenticate(Request $req)
    {
        $user = Client::where([
            ['email',$req->email],
            ['password', $req->password]
        ])->first();

        return $user;
    }

    public function create (Request $req)
    {
        $client = $req->client;
        $item = new Client($client);
        if($item->save())
            return $item;
        else
            return 0;
    }

    public function update (Request $req, $id)
    {
        $client = $req->client;
        $item = Client::where('id', $id)->first();
        if($item->update($client))
            return $item;
        else
            return 0;
    }

    public function delete ($id)
    {
        try {
            return Client::where('id', $id)->delete();
        } catch (\Throwable $th) {
            return Client::where('id', $id)->update([
                'status' => 0
            ]);
        }
    }
}
