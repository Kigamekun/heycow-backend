<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

use Midtrans\Config;
use Midtrans\Snap;


class TransactionController extends Controller
{

    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }


    public function createCharge(Request $request)
    {
        $params = [
            'transaction_details' => [
                'order_id' => rand(),
                'gross_amount' => 10000,
            ],
            'credit_card' => [
                'secure' => true
            ],
            'customer_details' => [
                'first_name' => 'Reksa',
                'last_name' => 'Syahputra',
                'email' => 'reksa.prayoga1012@gmail.com',
                'phone' => '0895331493506',
            ],
        ];

        $snapToken = Snap::getSnapToken($params);
        return response()->json($snapToken);
    }
}
