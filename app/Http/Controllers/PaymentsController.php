<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Payments;
use App\Orders;
use App\Customers;
use App\OrderItems;

use App\Http\Controllers\Midtrans\Config;
use App\Http\Controllers\Midtrans\Transaction;

use App\Http\Controllers\Midtrans\ApiRequestor;
use App\Http\Controllers\Midtrans\CoreApi;
use App\Http\Controllers\Midtrans\Notification;
use App\Http\Controllers\Midtrans\Snap;
use App\Http\Controllers\Midtrans\SnapApiRequestor;

use App\Http\Controllers\Midtrans\Sanitizer;

class PaymentController extends Controller
{
    public function __construct()
    {
        
    }

    public function getAll()
    {
        $data = Payments::all();
        Log::info("Get all data Payments");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function getById($id)
    {
        $data = Payments::find($id);
        if(!$data)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed retrieve data","status" => false,"data"=> $data], 404);
        }
        Log::info("Get data Payments $data->full_name");
        return response()->json(["messages"=>"success retrieve data","status" => true,"data"=> $data], 200);
    }

    public function create(Request $request)
    {
        Config::$serverKey = 'SB-Mid-server-jMa1yoEHLCbuNPkScwv9LKwI';
        if(!isset(Config::$serverKey))
        {
            return "Please set your payment server key";
        }

        Config::$isSanitized = true;
        Config::$is3ds = true;

        $data_req = [
                "data"=> [
                  "attributes"=> [
                  "payment_type"=> "bank_transfer",
                  "gross_amount"=> 0,
                  "bank"=> "bni",
                  "order_id"=> 1
                  ]
                ]
        ];
        $item_list = [];
        $item_order = $this->get_items(15);
        for($i=0; $i < count($item_order); $i++)
        {
            $item_list['id'] = $item_order[$i]['id'];
            $item_list['price'] = $item_order[$i]['product']['price'];
            $item_list['quantity'] = $item_order[$i]['quantity'];
            $item_list['name'] = $item_order[$i]['product']['name'];
        }
        // return $item_list;
        $item_new[] = [
            'id' => "111",
            'price' => 20000,
            'quantity' => 4,
            'name' => "Majohn"
        ];
        $item_lagi[] = $item_list;

        $transaction_details = array(
            'order_id' => rand(),
            'gross_amount' => 0, // no decimal allowed for creditcard
        );
        
        $customer_details = $this->get_customer(15);
        $customer_details = array(
            'first_name'    => $customer_details->fullname,
            'last_name'     => "",
            'email'         => $customer_details->email,
            'phone'         => $customer_details->phone_number
        );
        // return $customer_details;
        $enable_payments = ['bni', 'bca'];
        $transaction = array(
            // 'enabled_payments' => $enable_payments,
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_lagi,
        );
            // Transaction::status(15);
        try {
            $snapToken = Snap::createTransaction($transaction);
            // $status = Transaction::status($transaction_details['order_id']);
            // return $status;
            return response()->json(['code' => 1 , 'message' => 'success' , 'result' => $snapToken]);
            // return ['code' => 1 , 'message' => 'success' , 'result' => $snapToken];
        } catch (\Exception $e) {
            dd($e);
            return ['code' => 0 , 'message' => 'failed'];
        }
    }

    public function get_items($idOrder)
    {
        $data = OrderItems::where('order_id', $idOrder)->with(array('product' => function($query)
        {
            $query->select();
        }))->get();

        return $data;
    }

    public function get_customer($idOrder)
    {
        $orders = Orders::find($idOrder);
        $customer = Customers::find($orders->user_id);

        return $customer;
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

        $customer = Payments::find($id);

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
        $data = Payments::find($id);
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