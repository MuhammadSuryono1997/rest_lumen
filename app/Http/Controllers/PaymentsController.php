<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Payments;
use App\Orders;
use App\Customers;
use App\OrderItems;
use App\NotifTable;

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
        $requestData = $request->all();
        // $data_req = [
        //         "data"=> [
        //           "attributes"=> [
        //           "payment_type"=> "bank_transfer",
        //           "gross_amount"=> 0,
        //           "bank"=> "bni",
        //           "order_id"=> 1
        //           ]
        //         ]
        // ];
        $id = Orders::find($requestData['data']['attributes']['order_id']);
        if(!$id)
        {
            Log::error("Data not found");
            return response()->json(["messages"=>"failed id order data","status" => false,"data"=> ''], 404);
        }
        $item_list = [];
        $item_order = $this->get_items($requestData['data']['attributes']['order_id']);
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
            'order_id' => $requestData['data']['attributes']['order_id'],
            'gross_amount' => 0, // no decimal allowed for creditcard
        );
        
        $customer_details = $this->get_customer($requestData['data']['attributes']['order_id']);
        $customer_details = array(
            'first_name'    => $customer_details->fullname,
            'last_name'     => "",
            'email'         => $customer_details->email,
            'phone'         => $customer_details->phone_number
        );
        // return $customer_details;
        $enable_payments = ['bank_transfer'];
        $transaction = array(
            'enabled_payments' => $enable_payments,
            'payment_typr' => 'bank_transfer',
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'bank_transfer'=> 'bni',
            'item_details' => $item_lagi,
        );
            // Transaction::status(15);
        try {
            $snapToken = Snap::createTransaction($transaction);
            // return $snapToken;
            // sleep(1);
            // $status = file_get_contents('https://api.sandbox.midtrans.com/v2/'.$transaction_details['order_id'].'/status');
            // return ApiRequestor::get(
            //     Config::getBaseUrl() . '/' . 15 . '/status',
            //     Config::$serverKey,
            //     false
            // );
            // return $status;
            // return json_decode($status, true);
            $gross_amount = 0;
            foreach ($transaction['item_details'] as $item) {
                $gross_amount += $item['quantity'] * $item['price'];
            }

            $saveOrder = new Payments();
            $saveOrder->order_id =  $transaction_details['order_id'];
            $saveOrder->transaction_id = '';
            $saveOrder->payment_type = $requestData['data']['attributes']['payment_type'];
            $saveOrder->gross_amount = $gross_amount;
            $saveOrder->transaction_time = '';
            $saveOrder->transaction_status = 'created';
            $saveOrder->save();

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


    public function update($id)
    {
        $data = $this->getById($id);
        $data = $data->data;
        $id_order = $data->order_id;
        Config::$serverKey = 'SB-Mid-server-jMa1yoEHLCbuNPkScwv9LKwI';
        if(!isset(Config::$serverKey))
        {
            return "Please set your payment server key";
        }

        Config::$isSanitized = true;
        Config::$is3ds = true;
        $url = "https://api.sandbox.midtrans.com/v2/". $id_order. "/status";
        $curl = curl_init("$url");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode(Config::$serverKey.':'),
            'Content-Type: application/json',
                'Accept: application/json',
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $res = response()->json($response);
        return $this->save_update($res, $id);
    }

    public function save_update($data,$id)
    {
        $data = json_decode($data);
        $orders = Orders::find($id);
        $orders->transaction_time = $data->transaction_time;
        $orders->transaction_status = $data->transaction_status;
        $orders->transaction_id = $data->transaction_id;
        $orders->save();

        return response()->json(["messages => 'sukses update data'"]);
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

    public function notif(Request $request)
    {
        $req = $request->all();
        $pay = Payments::where('order_id', $req['order_id'])->get();

        $pays = Payments::find($pay->id);
        if(!$pay)
        {
            return response()->json(["messages"=> "Id order not found","status"=>"error"]);
        }
        $pays->transaction_time = $req['transaction_time'];
        $pays->transaction_status = $req['transaction_status'];
        $pays->transaction_id = $req['transaction_id'];
        if($pays->save())
        {
            return response()->json(["messages"=> "Perubahan transaksi"], 200);
        }

    }
}