<?php

namespace App\Http\Controllers\Api;

use App\Models\RequestAngon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class RequestAngonControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $user = Auth::id();
        $data = RequestAngon::where('user_id', $user)->orWhere('peternak_id', $user);

        $limit = $_GET['limit'] ?? 10;

        if ($data->count() > 0) {
            $data = $data->paginate($limit);
            $custom = collect(['status' => 'success', 'statusCode' => 200, 'message' => 'Data berhasil diambil', 'data' => $data, 'timestamp' => now()->toIso8601String()]);
            return response()->json($custom, 200);
        } else {
            $custom = collect(['status' => 'error', 'statusCode' => 404, 'message' => 'Data tidak ditemukan', 'data' => null]);
            return response()->json($custom, 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestAngon $requestAngon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestAngon $requestAngon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestAngon $requestAngon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestAngon $requestAngon)
    {
        //
    }
}
