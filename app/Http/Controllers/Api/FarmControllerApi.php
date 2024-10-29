<?php

namespace App\Http\Controllers\Api;

use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class FarmControllerApi extends Controller
{
    // public function index()
    // {
    //     $farms = Farm::latest()->get();

    //     return response()->json([
    //         'message' => 'Data peternakan',
    //         'status' => 'success',
    //         'data' => $farms
    //     ]);
    // }

    public function index()
    {
        $limit = $_GET['limit'] ?? 10;
        $data = Farm::orderBy('id', 'DESC');
        if (isset($_GET['search'])) {
            $data = $data->where('name', 'like', '%' . $_GET['search'] . '%');
        }
        if ($data->count() > 0) {
            $data = $data->paginate($limit);
            $custom = collect(['status' => 'success', 'statusCode' => 200, 'message' => 'Data berhasil diambil', 'data' => $data, 'timestamp' => now()->toIso8601String()]);
            return response()->json($custom, 200);
        } else {
            $custom = collect(['status' => 'error', 'statusCode' => 404, 'message' => 'Data tidak ditemukan', 'data' => null]);
            return response()->json($custom, 200);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
            ], [
                'name.required' => 'Nama harus diisi',
                'address.required' => 'Alamat harus diisi',
            ]);

            // Create new farm after validation success
            $farm = Farm::create([
                'name' => $validatedData['name'],
                'address' => $validatedData['address'],
            ]);

            return response()->json([
                'message' => 'Farm berhasil ditambahkan',
                'status' => 'success',
                'data' => $farm
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch validation error and return response immediately
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }

    public function show($id)
    {
        $farm = Farm::find($id);

        if (!$farm) {
            return response()->json([
                'message' => 'Farm tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        return response()->json([
            'message' => 'Data farm',
            'status' => 'success',
            'data' => $farm
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $farm = Farm::find($id);

        if (!$farm) {
            return response()->json(['message' => 'Farm tidak ditemukan', 'status' => 'error'], 404);
        }

        try {
            // Validasi data input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
            ]);

            // Update farm jika validasi berhasil
            $farm->update($validatedData);

            return response()->json([
                'message' => 'Farm berhasil diupdate',
                'status' => 'success',
                'data' => $farm
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkapan error validasi dan langsung mengembalikan response error
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }

    public function destroy($id)
    {
        $farm = Farm::find($id);

        if (!$farm) {
            return response()->json([
                'message' => 'Farm tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $farm->delete();

        return response()->json([
            'message' => 'Farm berhasil dihapus',
            'status' => 'success'
        ], 200);
    }


    public function cattle($id) {
        $farms = Farm::with('cattle')->where('id',$id)->first();

        return response()->json([
            'message' => 'Data peternakan dengan sapi',
            'status' => 'success',
            'data' => $farms
        ]);
    }

    public function mostCattle() {
        // $farms = Farm::withCount('cattle')->orderBy('cattle_count', 'desc')->get();
        $farms = Farm::withCount('cattle')->orderBy('cattle_count', 'desc')->first();

        return response()->json([
            'message' => 'Data peternakan dengan sapi terbanyak',
            'status' => 'success',
            'data' => $farms
        ]);
    }
}