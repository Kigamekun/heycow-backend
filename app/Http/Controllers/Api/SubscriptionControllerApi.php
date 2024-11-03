<?php

namespace App\Http\Controllers\Api;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

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
            // Menentukan status berdasarkan tanggal akhir
            $validatedData['status'] = Carbon::parse($validatedData['end_date'])->isPast() ? 'expired' : $validatedData['status'];

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

        // Menentukan status berdasarkan tanggal akhir
        $validatedData['status'] = Carbon::parse($validatedData['end_date'])->isPast() ? 'expired' : $validatedData['status'];

        $subscription->update($validatedData);

        return response()->json(['status' => 'sukses', 'pesan' => 'Langganan berhasil diperbarui', 'data' => $subscription]);
    }

    // Mengecek status langganan pengguna
    public function checkStatus($userId)
    {
        $subscription = Subscription::where('user_id', $userId)->latest()->first();
        
        if (!$subscription) {
            return response()->json(['status' => 'inactive', 'message' => 'No active subscription found']);
        }

        if (Carbon::parse($subscription->end_date)->isPast()) {
            return response()->json(['status' => 'expired', 'message' => 'Your subscription has expired.']);
        }

        return response()->json(['status' => $subscription->status, 'end_date' => $subscription->end_date]);
    }

    // Mengambil daftar rencana langganan
    public function listPlans()
    {
        $plans = [
            [
                'name' => 'Free',
                'duration' => '30 days',
                'price' => 'Rp 0',
                'features' => ['Basic Feature'],
            ],
            [
                'name' => 'Pro',
                'duration' => '1 year',
                'price' => 'Rp 35.000/month',
                'features' => ['Basic Feature', '5 added features'],
            ],
            [
                'name' => 'Premium',
                'duration' => '2 years',
                'price' => 'Rp 65.000/month',
                'features' => ['Basic Feature', '9 added features'],
            ],
        ];
        
        return response()->json(['status' => 'success', 'data' => $plans]);
    }
    

    // Menginisiasi pembayaran langganan
    public function initiatePayment(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan' => 'required|string|in:Free,Pro,Premium',
            'payment_method' => 'required|string|in:BCA,QRIS,Dana',
        ]);

        // Logika untuk menghitung biaya berdasarkan rencana
        $price = match ($validatedData['plan']) {
            'Pro' => 35000,
            'Premium' => 65000,
            default => 0,
        };

        // Membuat langganan baru alih-alih transaksi
        $subscription = Subscription::create([
            'user_id' => $validatedData['user_id'],
            'type' => $validatedData['plan'],
            'start_date' => now(), // Set tanggal mulai
            'end_date' => now()->addDays(30), // Set tanggal akhir 30 hari ke depan
            'status' => 'active', // Status langganan
            'active' => 1, // Set aktif
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription initiated. Please proceed to payment.',
            'data' => $subscription,
        ]);
    }

    // Mengambil riwayat transaksi langganan pengguna
    public function transactionHistory($userId)
    {
        $subscriptions = Subscription::where('user_id', $userId)->get();
        
        if ($subscriptions->isEmpty()) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Tidak ada langganan ditemukan'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $subscriptions]);
    }

    // Memperbarui status langganan
    public function updateSubscriptionStatus($subscriptionId, Request $request)
    {
        $subscription = Subscription::find($subscriptionId);
        
        if (!$subscription) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Langganan tidak ditemukan'], 404);
        }

        // Validasi status baru
        $request->validate([
            'status' => 'required|string|in:active,expired,canceled', // Sesuaikan dengan enum status di model
        ]);

        $subscription->update(['status' => $request->status]);
        
        return response()->json(['status' => 'sukses', 'pesan' => 'Status langganan berhasil diperbarui']);
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
