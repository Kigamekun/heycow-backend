<?php

namespace App\Http\Controllers\Api;

use App\Models\RequestAngon;
use App\Models\Contract;
use App\Models\User;
use App\Models\Cattle;
use App\Models\Farm;
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
                $cattle_id = DB::table('cattle')->where('id', $item->cattle_id)->first();
                $cattle = Cattle::with(['iotDevice', 'breed', 'farm', 'healthRecords'])->findOrFail($cattle_id->id);
                // $cattle = DB::table('cattle')->where('id', $item->cattle_id)->first();
                // $breed = DB::table('breeds')->where('id', $cattle->breed_id)->first();

                 return [
                    'id' => $item->id,
                    'cattle_id'=> $item->cattle_id,
                    'user_id' => $item->user_id,
                    // 'breed' => $breed,
                    'title' => $title,
                    'cattle' => $cattle,
                    'peternak' => $peternak,
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

        // Mengambil data pengguna terkait (misal user yang mengajukan request)
        $user = $requestAngon->user; // Pastikan relasi `user()` sudah ada di model RequestAngon

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate contract code dan kontrak otomatis
        $prefix = 'CONTRACT';
        $length = 4;
        $date = date('Ymd');
        $randomString = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
        $contractCode = $prefix . '-' . $date . '-' . $randomString;

        $peternak = User::where('id',$requestAngon->peternak_id)->first();
        $farm = Farm::where('user_id',$peternak->id)->first();
        $cattle = Cattle::where('id',$requestAngon->cattle_id)->first();



        // Membuat kontrak otomatis setelah request di-approve
        $contract = new Contract();
        $contract->contract_code = $contractCode;
        $contract->request_id = $requestAngon->id;
        $contract->cattle_id = $requestAngon->cattle_id;
        $contract->farm_id = $farm->id;
        $contract->start_date = now()->toDateString();
        $contract->end_date = now()->addMonths($requestAngon->duration)->toDateString();
        $contract->rate = $peternak->upah;
        $contract->initial_weight = $cattle->birth_weight;
        $contract->initial_height = $cattle->birth_height;
        $contract->final_weight = null;
        $contract->final_height = null;
        $contract->status = 'pending';
        $contract->total_cost = $peternak->upah * $requestAngon->duration;
        $contract->snap_token = null;
        $contract->transaction_time = null;
        $contract->payment_type = null;
        $contract->payment_status_message = null;
        $contract->transaction_id = null;
        $contract->jumlah_pembayaran = null;
        $contract->payment_status = null;
        $contract->save();

        // Mengembalikan response sukses dengan data kontrak yang valid
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

            $pengangon = DB::table('users')->where('id', $request->peternak_id)->first();
            $cattle = DB::table('cattle')->where('id', $request->cattle_id)->first();


            $formattedDate = now()->addDays(3)->format('d F Y - H:i');


            $data = [
                'nama_pengangon' => $pengangon->name,
                'nama_sapi' => $cattle->name,
                'durasi' => $request->durasi,
                'tanggal' => $formattedDate,
                'biaya' => (integer)$pengangon->upah * $request->durasi,
            ];

        return response()->json([
            'message' => 'Data Request',
            'status' => 'success',
            'data' => $data
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
