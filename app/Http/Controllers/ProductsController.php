<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Products;
use App\Orders;

class CustomerController extends Controller
{
    public function __construct()
    {
        
    }

    public function getAll()
    {
        $data = Products::All();
        Log::info("Get all data Products");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function getById($id)
    {
        $data = Products::find($id);
        if(!$data)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> $data], 404);
        }
        Log::info("Get data Products");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function insert(Request $request)
    {
        $this->validate($request,
        [
            'name' => 'required',
            'price' => 'required'
        ]);
        $product = new Products();
        $product->name = $request->input('name');
        $product->price = $request->input('price');

        if($product->save())
        {
            Log::info("Success input product");
            return response()->json(
                [
                    "data"=>[
                        "attributes"=>[
                            "name" =>$request->input('name'),
                            "price" =>$request->input('price')
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
            'name' => 'required',
            'price' => 'required'
        ]);
        $product = Products::find($id);
        if(!$product)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> ''], 404);
        }
        $product->name = $request->input('name');
        $product->price = $request->input('price');

        if($product->save())
        {
            Log::info("Success input product");
            return response()->json(
                [
                    "data"=>[
                        "attributes"=>[
                            "name" =>$request->input('name'),
                            "price" =>$request->input('price')
                        ]
                    ]
                        ], 201
            );
        }

        Log::error("Data not found");
        return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> ''], 403);
    }

    public function delete($id)
    {
        $data = Products::find($id);
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