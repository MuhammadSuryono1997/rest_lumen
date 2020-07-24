<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Customers;
use App\Orders;

class CustomerController extends Controller
{
    public function __construct()
    {
        
    }

    public function getAll()
    {
        $data = Customers::all();
        Log::info("Get all data customers");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function getById($id)
    {
        $data = Customers::find($id);
        if(!$data)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> $data], 404);
        }
        Log::info("Get data customers $data->full_name");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function insert(Request $request)
    {
        $this->validate($request,
        [
            'data.attributes.full_name'=> 'required',
            'data.attributes.username'=> 'required',
            'data.attributes.email'=> 'required|email',
            'data.attributes.phone_number'=> 'required'
        ]);
        $customer = new Customers();
        $customer->fullname = $request->input('data.attributes.full_name');
        $customer->username = $request->input('data.attributes.username');
        $customer->email = $request->input('data.attributes.email');
        $customer->phone_number = $request->input('data.attributes.phone_number');

        if($customer->save())
        {
            Log::info("Success input customer");
            return response()->json(
                [
                    "data"=>[
                        "attributes"=>[
                            "full_name" =>$request->input('data.attributes.full_name'),
                            "username" =>$request->input('data.attributes.username'),
                            "email" =>$request->input('data.attributes.email'),
                            "phone_number" =>$request->input('data.attributes.phone_number')
                        ]
                    ]
                        ], 201
            );
        }
    }


    public function update(Request $request, $id)
    {
        $this->validate($request,
        [
            'data.attributes.full_name'=> 'required',
            'data.attributes.username'=> 'required',
            'data.attributes.email'=> 'required|email',
            'data.attributes.phone_number'=> 'required'
        ]);

        $customer = Customers::find($id);

        if(!$customer)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> ''], 404);
        }
        $customer->fullname = $request->input('data.attributes.full_name');
        $customer->username = $request->input('data.attributes.username');
        $customer->email = $request->input('data.attributes.email');
        $customer->phone_number = $request->input('data.attributes.phone_number');

        if($customer->save())
        {
            Log::info("Success input customer");
            return response()->json(
                [
                    "data"=>[
                        "attributes"=>[
                            "full_name" =>$request->input('data.attributes.full_name'),
                            "username" =>$request->input('data.attributes.username'),
                            "email" =>$request->input('data.attributes.email'),
                            "phone_number" =>$request->input('data.attributes.phone_number')
                        ]
                    ]
                        ], 201
            );
        }
    }

    public function delete($id)
    {
        $data = Customers::find($id);
        if(!$data)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed delete data","status" => false,"data"=> ''], 404);
        }

        if($data->delete())
        {
            Log::info('Data success delete');
            return response()->json(["messages"=>"success delete data","status" => true,"data"=> $data], 200);
        }
    }
}