<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cattle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CattleControllerApi extends Controller
{
    public function index()
    {
        $cattles = Cattle::latest()->get();
        return response()->json([
            'message' => 'Data Sapi',
            'status' => 'success',
            'data' => $cattles
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'breed' => 'required|string|max:255',
                'status' => 'required|in:alive,dead,sold',
                'birth_date' => 'required|date',
                'birth_weight' => 'required|numeric',
                'farm_id' => 'required|exists:farms,id',
                'user_id' => 'required|exists:users,id',
                'iot_device_id' => 'required|exists:iot_devices,id',
            ], [
                'name.required' => 'Nama sapi harus diisi',
                'breed.required' => 'Ras sapi harus diisi',
                'status.required' => 'Status sapi harus diisi',
                'birth_date.required' => 'Tanggal lahir sapi harus diisi',
                'birth_weight.required' => 'Berat lahir sapi harus diisi',
                'farm_id.required' => 'ID peternakan harus diisi',
                'user_id.required' => 'ID pengguna harus diisi',
                'iot_device_id.required' => 'ID perangkat IoT harus diisi',
            ]);

            // Log data validasi untuk debugging
            Log::info('Cattle validation successful', $validatedData);

            // Simpan data sapi
            $cattle = Cattle::create($validatedData);

            return response()->json([
                'message' => 'Sapi berhasil ditambahkan',
                'status' => 'success',
                'data' => $cattle
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangani kesalahan validasi
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }

    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $cattle = Cattle::find($id);

        if (!$cattle) {
            return response()->json([
                'message' => 'Cattle tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        return response()->json([
            'message' => 'Data Sapi ditemukan',
            'status' => 'success',
            'data' => $cattle
        ]);
    }

    public function update(Request $request, $id)
    {
        $cattle = Cattle::find($id);

        if (!$cattle) {
            return response()->json([
                'message' => 'Cattle tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        try {
            // Validasi data input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'breed' => 'required|string|max:255',
                'status' => 'required|in:alive,dead,sold',
                'birth_date' => 'required|date',
                'birth_weight' => 'required|numeric',
                'farm_id' => 'required|exists:farms,id',
                'user_id' => 'required|exists:users,id',
                'iot_device_id' => 'required|exists:iot_devices,id',
            ], [
                'name.required' => 'Nama sapi harus diisi',
                'breed.required' => 'Ras sapi harus diisi',
                'status.required' => 'Status sapi harus diisi',
                'birth_date.required' => 'Tanggal lahir sapi harus diisi',
                'birth_weight.required' => 'Berat lahir sapi harus diisi',
                'farm_id.required' => 'ID peternakan harus diisi',
                'user_id.required' => 'ID pengguna harus diisi',
                'iot_device_id.required' => 'ID perangkat IoT harus diisi',
            ]);

            // Update data sapi jika validasi berhasil
            $cattle->update($validatedData);

            return response()->json([
                'message' => 'Sapi berhasil diupdate',
                'status' => 'success',
                'data' => $cattle
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkapan error validasi
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $cattle = Cattle::find($id);

        if (!$cattle) {
            return response()->json([
                'message' => 'Cattle tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $cattle->delete();

        return response()->json([
            'message' => 'Cattle berhasil dihapus',
            'status' => 'success'
        ], 200);
    }
}
