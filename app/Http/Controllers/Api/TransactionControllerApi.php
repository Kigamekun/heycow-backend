<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Transaction,Contract,RequestAngon,User};
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


    public function createCharge(Request $request,$id)
    {

        $contract = Contract::where('id',$id)->first();
        $req = RequestAngon::where('id',$contract->request_id)->first();
        $user = User::where('id',$req->user_id)->first();

        $snapToken = $contract->snap_token;
        if (is_null($snapToken)) {
            $params = [
                'transaction_details' => [
                    'order_id' => rand(),
                    'gross_amount' => $contract->total_cost,
                ],
                'credit_card' => [
                    'secure' => true
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $contract->snap_token = $snapToken;
            $contract->save();
        }


        return view('api.charge', compact('snapToken','id'));
    }

    public function refresh(){
        return redirect()->secure(route('history'));
    }

    public function history(){
        return 'selesai';
    }

    public function cst(Request $request)
    {

        try {
            Contract::where('id', $request->id)->update([
                'transaction_time' => $request->result['transaction_time'],
                'payment_type' => $request->result['payment_type'] . "-" . $request->result['bank'],
                'payment_status_message' => $request->result['status_message'],
                'transaction_id' => $request->result['transaction_id'],

                'jumlah_pembayaran' => $request->result['gross_amount'],
            ]);
        } catch (\Throwable $th) {
            Contract::where('id', $request->id)->update([
                'transaction_time' => $request->result['transaction_time'],
                'payment_type' => $request->result['payment_type'],
                'payment_status_message' => $request->result['status_message'],
                'transaction_id' => $request->result['transaction_id'],

                'jumlah_pembayaran' => $request->result['gross_amount'],
            ]);
        }
        if ($request->status == "success") {
            Contract::where('id', $request->id)->update([
                'payment_status' => 2,
                'status' => 'active'

            ]);
        } elseif ($request->status == 'pending') {
            Contract::where('id', $request->id)->update([
                'paymment_status' => 1,
            ]);
        } elseif ($request->status == 'error') {
            Contract::where('id', $request->id)->update([
                'paymment_status' => 4,
            ]);
        }
        // Mail::to(Auction::where('id', $request->id)->first()->user->email)->send(new WinnerAuctionMail($request->auction));
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
            'herder_name' => 'required|string',
            'cattle_name' => 'required|string',
            'duration' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'activity' => 'required|string',
            'cattle_count' => 'required|integer|min:0',
        ], [
            'user_id.required' => 'Kolom user_id belum terisi, tolong diisi.',
            'user_id.exists' => 'User tidak ditemukan dalam sistem.',
            'amount.required' => 'Kolom amount belum terisi, tolong diisi.',
            'amount.numeric' => 'Kolom amount harus berupa angka.',
            'type.required' => 'Kolom type belum terisi, tolong diisi.',
            'type.in' => 'Kolom type hanya boleh berisi "credit" atau "debit".',
            'status.required' => 'Kolom status belum terisi, tolong diisi.',
            'status.in' => 'Kolom status hanya boleh berisi "pending", "completed", atau "failed".',
            'herder_name.required' => 'Kolom herder_name belum terisi, tolong diisi.',
            'cattle_name.required' => 'Kolom cattle_name belum terisi, tolong diisi.',
            'duration.required' => 'Kolom duration belum terisi, tolong diisi.',
            'cost.required' => 'Kolom cost belum terisi, tolong diisi.',
            'cost.numeric' => 'Kolom cost harus berupa angka.',
            'activity.required' => 'Kolom activity belum terisi, tolong diisi.',
            'cattle_count.required' => 'Kolom cattle_count belum terisi, tolong diisi.',
            'cattle_count.integer' => 'Kolom cattle_count harus berupa angka.',
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


    // Memperbarui transaksi
    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Transaksi tidak ditemukan'], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'nullable|numeric|min:0',
            'type' => 'nullable|string|in:credit,debit',
            'status' => 'nullable|string|in:pending,completed,failed',
            'herder_name' => 'nullable|string',
            'cattle_name' => 'nullable|string',
            'duration' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'activity' => 'nullable|string',
            'cattle_count' => 'nullable|integer|min:0',
        ]);

        $transaction->update($validatedData);

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Transaksi berhasil diperbarui',
            'data' => $transaction
        ]);
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

    // Mengambil transaksi berdasarkan user_id
    public function getUserTransactions($userId)
    {
        $transactions = Transaction::where('user_id', $userId)->get();
        return response()->json([
            'status' => 'sukses',
            'data' => $transactions,
        ]);
    }

    // Menambahkan metode konfirmasi transaksi di TransactionControllerApi
    public function confirm($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Transaksi tidak ditemukan'], 404);
        }

        // Update status transaksi menjadi completed
        $transaction->update(['status' => 'completed']);

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Transaksi berhasil dikonfirmasi',
            'data' => $transaction,
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
