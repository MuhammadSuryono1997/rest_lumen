<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Midtrans\Config;

use App\Http\Controllers\Transaction;

use App\Http\Controllers\Midtrans\ApiRequestor;
use App\Http\Controllers\Midtrans\CoreApi;
use App\Http\Controllers\Midtrans\Notification;
use App\Http\Controllers\Midtrans\Snap;
use App\Http\Controllers\Midtrans\SnapApiRequestor;

use App\Http\Controllers\Midtrans\Sanitizer;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Request;

class MidtransController extends Controller
{
    public function getSnapToken(Request $request){
        $item_list = array();
        $amount = 0;
        Config::$serverKey = 'SB-Mid-server-jMa1yoEHLCbuNPkScwv9LKwI';
        if(!isset(Config::$serverKey))
        {
            return "Please set your payment server key";
        }

        Config::$isSanitized = true;
        Config::$is3ds = true;

        $item_list[] = [
                'id' => "111",
                'price' => 20000,
                'quantity' => 1,
                'name' => "Majohn"
        ];

        $transaction_details = array(
            'order_id' => rand(),
            'gross_amount' => 20000, // no decimal allowed for creditcard
        );

        // optional
        $item_details = $item_list;

        $billing_address = array(
            'first_name'    => "Andri",
            'last_name'     => "Litani",
            'address'       => "Mangga 20",
            'city'          => "Jakarta",
            'postal_code'   => "16602",
            'phone'         => "081122334455",
            'country_code'  => 'IDN'
        );

        $shipping_address = array(
            'first_name'    => "Obet",
            'last_name'     => "Supriadi",
            'address'       => "Manggis 90",
            'city'          => "Jakarta",
            'postal_code'   => "16601",
            'phone'         => "08113366345",
            'country_code'  => 'IDN'
        );

        $customer_details = array(
            'first_name'    => "Andri",
            'last_name'     => "Litani",
            'email'         => "andri@litani.com",
            'phone'         => "081122334455",
            'billing_address'  => $billing_address,
            'shipping_address' => $shipping_address
        );

        $enable_payments = array();

        // Fill transaction details
        $transaction = array(
            'enabled_payments' => $enable_payments,
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details,
        );

        try {
            $snapToken = Snap::getSnapToken($transaction);
            // return response()->json($snapToken);
            return ['code' => 1 , 'message' => 'success' , 'result' => $snapToken];
        } catch (\Exception $e) {
            dd($e);
            return ['code' => 0 , 'message' => 'failed'];
        }
    }
}


