<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ContractControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::id();

        $data = Contract::join('request_ngangons', 'request_ngangons.id', '=', 'contracts.request_id')
            ->where(function($query) use ($user) {
                $query->where('request_ngangons.user_id', $user)
                      ->orWhere('request_ngangons.peternak_id', $user);
            })
            ->select('contracts.*');

        $limit = request()->get('limit', 10);

        if ($data->exists()) {
            $data = $data->paginate($limit)->through(function ($item) use ($user) {
                return [
                    'id' => $item->id,
                    'title' => "Contract " . $item->contract_code,
                    'status' => $item->status,
                    'tanggal' => $item->created_at->toIso8601String(),
                ];
            });

            $custom = [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Data berhasil diambil',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ];

            return response()->json($custom, 200);
        } else {
            $custom = [
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'Data tidak ditemukan',
                'data' => null
            ];

            return response()->json($custom, 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'Data Contract tidak ditemukan',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'statusCode' => 200,
            'message' => 'Data Contract ditemukan',
            'data' => $contract
        ], 200);
    }

    /**
     * Mengembalikan kontrak yang sudah selesai.
     */
    public function returnContract($id)
    {
        // Ambil kontrak berdasarkan ID
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'Contract not found',
                'data' => null
            ], 404);
        }

        // Pastikan kontrak belum dikembalikan
        if ($contract->status === 'returned') {
            return response()->json([
                'status' => 'error',
                'statusCode' => 400,
                'message' => 'Contract has already been returned',
                'data' => null
            ], 400);
        }

        // Update status kontrak menjadi returned
        $contract->status = 'returned';
        $contract->end_date = now(); // Set tanggal pengembalian
        $contract->save();

        return response()->json([
            'status' => 'success',
            'statusCode' => 200,
            'message' => 'Contract returned successfully',
            'data' => $contract
        ], 200);
    }
}
