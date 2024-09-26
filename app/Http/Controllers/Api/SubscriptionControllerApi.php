<?php

namespace App\Http\Controllers\Api;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class SubscriptionControllerApi extends Controller
{
    // Mengambil semua langganan
    public function index()
    {
        $subscriptions = Subscription::all();
        return response()->json([
            'status' => 'sukses',
            'data' => $subscriptions,
        ]);
    }

    // Menyimpan langganan baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|in:Basic,Premium',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:active,expired,canceled',
            'active' => 'required|boolean',
        ]);

        try {
            $subscription = Subscription::create($validatedData);
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Langganan berhasil ditambahkan',
                'data' => $subscription,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal menambahkan langganan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mengambil langganan spesifik
    public function show($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Langganan tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'sukses', 'data' => $subscription]);
    }

    // Memperbarui langganan
    public function update(Request $request, $id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Langganan tidak ditemukan'], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|in:Basic,Premium',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:active,expired,canceled',
            'active' => 'required|boolean',
        ]);

        $subscription->update($validatedData);

        return response()->json(['status' => 'sukses', 'pesan' => 'Langganan berhasil diperbarui', 'data' => $subscription]);
    }

    // Menghapus langganan
    public function destroy($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Langganan tidak ditemukan'], 404);
        }

        $subscription->delete();
        return response()->json(['status' => 'sukses', 'pesan' => 'Langganan berhasil dihapus']);
    }
}
