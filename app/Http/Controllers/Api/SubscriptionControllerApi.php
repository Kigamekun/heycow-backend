<?php

namespace App\Http\Controllers\Api;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;

class SubscriptionControllerApi extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::latest()->get();

        return response()->json([
            'message' => 'Data langganan',
            'status' => 'success',
            'data' => $subscriptions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|in:Basic,Premium',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:active,expired,canceled',
            'active' => 'required|boolean',
        ]);

        $subscription = Subscription::create($request->all());

        return response()->json([
            'message' => 'Langganan berhasil ditambahkan',
            'status' => 'success',
            'data' => $subscription
        ], 201);
    }

    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response()->json(['message' => 'Langganan tidak ditemukan', 'status' => 'error'], 404);
        }

        return response()->json($subscription);
    }

    public function update(Request $request, $id)
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response()->json(['message' => 'Langganan tidak ditemukan', 'status' => 'error'], 404);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|in:Basic,Premium',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:active,expired,canceled',
            'active' => 'required|boolean',
        ]);

        $subscription->update($request->all());

        return response()->json([
            'message' => 'Langganan berhasil diupdate',
            'status' => 'success',
            'data' => $subscription
        ], 200);
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response()->json(['message' => 'Langganan tidak ditemukan', 'status' => 'error'], 404);
        }

        $subscription->delete();

        return response()->json([
            'message' => 'Langganan berhasil dihapus',
            'status' => 'success'
        ], 200);
    }
}
