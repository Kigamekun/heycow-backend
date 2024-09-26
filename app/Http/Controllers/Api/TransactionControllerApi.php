<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionControllerApi extends Controller
{
    // Mengambil semua transaksi
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json([
            'status' => 'sukses',
            'data' => $transactions,
        ]);
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
