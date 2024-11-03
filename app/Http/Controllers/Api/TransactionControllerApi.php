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
