<?php

namespace App\Http\Controllers\Api;

use App\Models\HelpCenter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class HelpCenterControllerApi extends Controller
{

public function store(Request $request)
{
    try {
        // Validasi data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'question' => 'required|string',
        ], [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'question.required' => 'Detail pertanyaan harus diisi',
        ]);

        // Kirim email dengan detail yang sesuai menggunakan tampilan
        Mail::send('emails.help_center', [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'question' => $validatedData['question']
        ], function ($message) {
            $message->to('ardien0318@gmail.com') // Ganti dengan email Anda
                    ->subject('Pesan Baru dari Help Center');
        });

        return response()->json([
            'message' => 'Pertanyaan berhasil dikirim',
            'status' => 'success',
            'statusCode' => 200
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'errors' => $e->errors(),
            'status' => 'error'
        ], 422);
    }
}



    public function index()
    {
        $helpCenters = HelpCenter::latest()->get();

        return response()->json([
            'message' => 'Data Help Center',
            'status' => 'success',
            'data' => $helpCenters
        ]);
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
