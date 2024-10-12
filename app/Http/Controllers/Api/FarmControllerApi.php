<?php

namespace App\Http\Controllers\Api;

use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class FarmControllerApi extends Controller
{
    public function index()
    {
        $farms = Farm::latest()->get();

        return response()->json([
            'message' => 'Data peternakan',
            'status' => 'success',
            'data' => $farms
        ]);
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
}
