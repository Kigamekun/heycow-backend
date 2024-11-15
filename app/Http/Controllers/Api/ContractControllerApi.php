<?php

namespace App\Http\Controllers\Api;

use App\Models\{Contract,Cattle};
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

         $limit = $_GET['limit'] ?? 10;


        if ($data->exists()) {
            $data = $data->paginate($limit)->map(function ($item) use ($user) {
                return [
                    'id' => $item->id,
                    'title' =>  $item->contract_code,
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

        $contract->farmName = $contract->farm->name;
        $contract->cattleName = $contract->cattle->name;
        $contract->farmAddress = $contract->farm->address ?? '-';
        $contract->pengangonPhone = $contract->request->peternak->phone ?? '-';
        $contract->pengangonName = $contract->request->peternak->name ?? '-';
        $contract->pelangganPhone = $contract->request->user->phone ?? '-';
        $contract->pengangonFee = 'Rp. '.$contract->total_cost;


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
    public function returnContract(Request $request, $id)
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

    // Validasi input untuk weight, height, dan rate
    $request->validate([
        'weight' => 'required|numeric|min:0',
        'height' => 'required|numeric|min:0',
        'rate' => 'required|numeric|min:0'
    ]);

    // Ambil data cattle terkait
    $cattle = $contract->cattle;

    if (!$cattle) {
        return response()->json([
            'status' => 'error',
            'statusCode' => 404,
            'message' => 'Cattle not found',
            'data' => null
        ], 404);
    }

    // Simpan birth_weight dan birth_height lama ke dalam history_records
    \DB::table('history')->insert([
        'cattle_id' => $cattle->id,
        'user_id' => Auth::id(),
        'record_type' => 'weight',
        'old_value' => $cattle->birth_weight,
        'new_value' => $request->weight,
        'message' => 'Updated weight during contract return',
    ]);

    \DB::table('history')->insert([
        'cattle_id' => $cattle->id,
        'user_id' => Auth::id(),
        'record_type' => 'height',
        'old_value' => $cattle->birth_height,
        'new_value' => $request->height,
        'message' => 'Updated height during contract return',
    ]);

    // Update birth_weight dan birth_height pada Cattle dengan data baru dari request
    $cattle->birth_weight = $request->weight;
    $cattle->birth_height = $request->height;
    $cattle->save();

    // Update status kontrak menjadi returned, serta simpan rate baru
    $contract->final_weight = $request->weight;
    $contract->final_height = $request->height;
    $contract->rate = $request->rate;
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
