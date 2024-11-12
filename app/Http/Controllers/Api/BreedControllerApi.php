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

    public function store(Request $request)
    {
        // Validasi data yang diterima
        $validatedData = $request->validate([
            'name' => 'required|string',
            'country' => 'required|string',
            'type' => 'required|string',
            'characteristics' => 'required|string',
        ]);

        // Simpan data ke database
        $breed = Breed::create($validatedData);

        return response()->json([
            'message' => 'Breed successfully created',
            'data' => $breed
        ], 201);
    }



    // API untuk update breed
    public function update(Request $request, $id)
    {
        // Validasi data yang diterima
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string',
            'country' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'characteristics' => 'sometimes|required|string',
        ]);

        // Cari breed berdasarkan ID
        $breed = Breed::find($id);

        if (!$breed) {
            return response()->json([
                'message' => 'Ras sapi tidak ditemukan',
                'status' => 'error',
            ], 404);
        }

        // Update breed dengan data yang divalidasi
        $breed->update($validatedData);

        return response()->json([
            'message' => 'Breed successfully updated',
            'status' => 'success',
            'data' => $breed,
        ], 200);
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
