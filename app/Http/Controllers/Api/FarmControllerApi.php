<?php

namespace App\Http\Controllers\Api;

use App\Models\{Farm, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;

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
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $farm = Farm::create([
            'name' => $request->name,
            'address' => $request->address,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'message' => 'Farm berhasil ditambahkan',
            'status' => 'success',
            'data' => $farm
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $farm = Farm::find($id);

        if (!$farm) {
            return response()->json(['message' => 'Farm tidak ditemukan', 'status' => 'error'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $farm->update([
            'name' => $request->name,
            'address' => $request->address,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'message' => 'Farm berhasil diupdate',
            'status' => 'success',
            'data' => $farm
        ], 200);
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
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
