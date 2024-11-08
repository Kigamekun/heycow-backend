<?php

namespace App\Http\Controllers\Api;

use App\Models\RequestAngon;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class RequestAngonControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index()
     {
         $user = Auth::id();
         $data = RequestAngon::with(['user', 'cattle'])
                     ->where('user_id', $user)
                     ->orWhere('peternak_id', $user);

         $limit = $_GET['limit'] ?? 10;

         if ($data->count() > 0) {
             $data = $data->paginate($limit)->map(function ($item) use ($user) {
                 $is_pengangon = $item->peternak_id === $user;

                 $peternak = DB::table('users')->where('id', $item->peternak_id)->first();
                 $user = DB::table('users')->where('id', $item->user_id)->first();

                 $title = $is_pengangon
                     ? "Permintaan mengangon dari " . $item->user->name . ": " . $item->cattle->name
                     : "Request Angon " . $item->cattle->name . " ke " . $item->peternak->name;

                 return [
                    'id' => $item->id,
                     'title' => $title,
                     'is_pengangon' => $is_pengangon,
                     'status' => $item->status,
                     'tanggal' => $item->created_at->toIso8601String(),
                 ];
             });

             $custom = collect([
                 'status' => 'success',
                 'statusCode' => 200,
                 'message' => 'Data berhasil diambil',
                 'data' => $data,
                 'timestamp' => now()->toIso8601String()
             ]);

             return response()->json($custom, 200);
         } else {
             $custom = collect([
                 'status' => 'error',
                 'statusCode' => 404,
                 'message' => 'Data tidak ditemukan',
                 'data' => null
             ]);

             return response()->json($custom, 200);
         }
     }

     public function approveRequest($id)
{
    // Ambil data request berdasarkan ID
    $requestAngon = RequestAngon::find($id);

    if (!$requestAngon) {
        return response()->json(['message' => 'Request not found'], 404);
    }

    // Jika status sudah approved, kembalikan response dengan kode 200
    if ($requestAngon->status == 'approved') {
        return response()->json(['message' => 'Request already approved'], 200);
    }

    // Update status menjadi approved
    $requestAngon->status = 'approved';
    $requestAngon->save();

    $prefix = 'CONTRACT'; $length = 6;
    $date = date('Ymd');

    // Generate a random alphanumeric string of the desired length
    $randomString = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);

    // Combine the prefix, date, and random string to form the contract code
    $contractCode = $prefix . '-' . $date . '-' . $randomString;
    // Membuat kontrak otomatis setelah request di-approve
    $contract = new Contract();
    $contract->contract_code=$contractCode;
    $contract->request_id = $requestAngon->id;
    $contract->cattle_id = $requestAngon->cattle_id;
    $contract->farm_id = $requestAngon->peternak_id;
    $contract->start_date = now()->toDateString();
    $contract->end_date = now()->addMonths(1)->toDateString(); // Misal kontrak 1 bulan
    $contract->rate = 100.00; // Tentukan rate yang sesuai
    $contract->initial_weight = 0.00; // Tentukan nilai berat awal
    $contract->initial_height = 0.00; // Tentukan nilai tinggi awal
    $contract->status = 'pending'; // Status kontrak draft atau pending
    $contract->save();

    // Mengembalikan response sukses
    return response()->json([
        'message' => 'Request approved and contract created',
        'contract' => $contract
    ], 200);
}

public function rejectRequest($id)
{
    // Ambil data request berdasarkan ID
    $requestAngon = RequestAngon::find($id);

    if (!$requestAngon) {
        return response()->json(['message' => 'Request not found'], 404);
    }

    // Perbarui status menjadi declined
    $requestAngon->status = 'declined';
    $requestAngon->save();

    return response()->json([
        'message' => 'Request declined successfully',
        'request' => $requestAngon
    ], 200);
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
        RequestAngon::create(
            [
                'user_id' => Auth::id(),
                'peternak_id' => $request->peternak_id,
                'cattle_id' => $request->cattle_id,
                'duration' => $request->durasi,
                'status' => 'pending',
            ]);

        return response()->json([
            'message' => 'Data Request',
            'status' => 'success',
        ], 200);
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
