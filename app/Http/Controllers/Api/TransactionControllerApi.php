<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


use Midtrans\Config;
use Midtrans\Snap;


class TransactionControllerApi extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }


    // Mengambil semua transaksi
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json([
            'status' => 'sukses',
            'data' => $transactions,
        ]);
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
        return view('api.charge', compact('snapToken'));
    }


    public function cst(Request $request)
    {

        // dd($request->all());
        // try {
        //     Auction::where('id', $request->auction)->update([
        //         'transaction_time' => $request->result['transaction_time'],
        //         'payment_type' => $request->result['payment_type'] . "-" . $request->result['bank'],
        //         'payment_status_message' => $request->result['status_message'],
        //         'transaction_id' => $request->result['transaction_id'],

        //         'jumlah_pembayaran' => $request->result['gross_amount'],
        //     ]);
        // } catch (\Throwable $th) {
        //     Auction::where('id', $request->auction)->update([
        //         'transaction_time' => $request->result['transaction_time'],
        //         'payment_type' => $request->result['payment_type'],
        //         'payment_status_message' => $request->result['status_message'],
        //         'transaction_id' => $request->result['transaction_id'],

        //         'jumlah_pembayaran' => $request->result['gross_amount'],
        //     ]);
        // }
        // if ($request->status == "success") {
        //     Auction::where('id', $request->auction)->update([
        //         'payment_status' => 2,
        //     ]);
        // } elseif ($request->status == 'pending') {
        //     Auction::where('id', $request->auction)->update([
        //         'paymment_status' => 1,
        //     ]);
        // } elseif ($request->status == 'error') {
        //     Auction::where('id', $request->auction)->update([
        //         'paymment_status' => 4,
        //     ]);
        // }


        // Product::where('id', Auction::where('id', $request->auction)->first()->product_id)->update([
        //     'status' => '0',
        // ]);


        // Mail::to(Auction::where('id', $request->auction)->first()->user->email)->send(new WinnerAuctionMail($request->auction));

        return response()->json(['message' => 'Update Transaction', 'status' => 'success'], 200);
    }

    public function payFinish()
    {
        return view('api.pay-finish');
    }








    // Menyimpan transaksi baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:credit,debit',
            'status' => 'required|string|in:pending,completed,failed',
        ]);

        try {
            $transaction = Transaction::create($validatedData);
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Transaksi berhasil dibuat',
                'data' => $transaction,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal membuat transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mengambil transaksi spesifik
    public function show($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'sukses', 'data' => $transaction]);
    }

    // Memperbarui transaksi
    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Transaksi tidak ditemukan'], 404);
        }

        $validatedData = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'type' => 'nullable|string|in:credit,debit',
            'status' => 'nullable|string|in:pending,completed,failed',
        ]);

        $transaction->update($validatedData);

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Transaksi berhasil diperbarui',
            'data' => $transaction
        ]);
    }

    // Menghapus transaksi
    public function destroy($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Transaksi tidak ditemukan'], 404);
        }

        $transaction->delete();
        return response()->json(['status' => 'sukses', 'pesan' => 'Transaksi berhasil dihapus']);
    }
}
