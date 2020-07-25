<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Customers;
use App\OrderItems;
use App\Orders;
use App\Products;

class OrderController extends Controller
{
    public function __construct()
    {
        
    }

    public function getAll()
    {
        $data = Orders::with(array('orderitem' => function($query)
        {
            $query->select();
        }))->get();
        
        Log::info("Get all data Orders");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function getById($id)
    {
        $data = Orders::find($id);
        if(!$data)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> $data], 404);
        }
        Log::info("Get data Orders");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function insert(Request $request)
    {
        $request_data = $request->all();
        $order = new Orders();
        $order->user_id = $request_data['data']['attributes']['user_id'];
        $order->order_status = 'create';
        $order->save();

            $data_product = $request_data['data']['attributes']['order_detail'];
            for($i=0; $i<count($data_product); $i++)
            {
                $product = new OrderItems();
                $product->order_id = $order->id;
                $product->product_id = $data_product[$i]['product_id'];
                $product->quantity = $data_product[$i]['quantity'];
                $product->save();
            }
            return $request;
    }


    public function update(Request $request, $id)
    {
        $order = Orders::find($id);
        if(!$order)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed update data","status" => false,"data"=> ''], 404);
        }
        $request_data = $request->all();
        $order->user_id = $request_data['data']['attributes']['user_id'];
        $order->order_status = $request_data['data']['attributes']['status'];
        $order->save();

        return $request;
    }

    public function delete($id)
    {
        $data = Orders::find($id);
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