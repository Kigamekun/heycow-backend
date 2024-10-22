<?php

namespace App\Http\Controllers\Api;

use App\Models\HelpCenter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HelpCenterControllerApi extends Controller
{
    public function index()
    {
        $helpCenters = HelpCenter::latest()->get();

        return response()->json([
            'message' => 'Data Help Center',
            'status' => 'success',
            'data' => $helpCenters
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'question_option' => 'required|string',
                'question_details' => 'required|string',
            ], [
                'name.required' => 'Nama harus diisi',
                'email.required' => 'Email harus diisi',
                'question_option.required' => 'Opsi pertanyaan harus diisi',
                'question_details.required' => 'Detail pertanyaan harus diisi',
            ]);

            // Create new Help Center entry after validation success
            $helpCenter = HelpCenter::create($validatedData);

            return response()->json([
                'message' => 'Pertanyaan berhasil dikirim',
                'status' => 'success',
                'data' => $helpCenter
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }

    public function destroy($id)
    {
        $helpCenter = HelpCenter::find($id);

        if (!$helpCenter) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $helpCenter->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus',
            'status' => 'success'
        ], 200);
    }
}
