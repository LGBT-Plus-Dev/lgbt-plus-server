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
            ['username',$req->username],
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

    public function updateImage (Request $req)
    {
        $item = Client::where('id', $req->id)->first();

        $image_result = false;
        
        $result = false;

        if($req->hasFile('image')) {

            $path = 'storage/images/client/'.$item->id;

            if(file_exists($path)) {
                array_map('unlink', glob("$path/*.*"));
            }

            $original_filename = $req->file('image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $destination_path = 'storage/images/client/'.$item->id.'/';
            $image = 'profile.' . $file_ext;

            if ($req->file('image')->move($destination_path, $image)) {
                $image_result = $destination_path.$image;
                $result = $item->update([
                    "image_uri" => $image_result
                ]);
            }
        }
        else {
            return "No Image";
        }

        if($result) {
            return $item;
        }
        else {
            return "Failed";
        }
    }
}
