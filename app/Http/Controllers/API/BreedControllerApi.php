<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Breed;
use Illuminate\Http\Request;

class BreedControllerApi extends Controller
{
    // API untuk melihat semua breed
    public function index()
    {
        $breeds = Breed::all();

        return response()->json([
            'message' => 'Data semua ras sapi',
            'status' => 'success',
            'data' => $breeds,
        ], 200);
    }

    // API untuk menambah breed baru
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
            ], [
                'name.required' => 'Nama ras sapi harus diisi',
            ]);

            $breed = Breed::create([
                'name' => $validatedData['name'],
            ]);

            return response()->json([
                'message' => 'Ras sapi berhasil ditambahkan',
                'status' => 'success',
                'data' => $breed
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }

    // API untuk menghapus breed
    public function destroy($id)
    {
        $breed = Breed::find($id);

        if (!$breed) {
            return response()->json([
                'message' => 'Ras sapi tidak ditemukan',
                'status' => 'error',
            ], 404);
        }

        $breed->delete();

        return response()->json([
            'message' => 'Ras sapi berhasil dihapus',
            'status' => 'success',
        ], 200);
    }
}
