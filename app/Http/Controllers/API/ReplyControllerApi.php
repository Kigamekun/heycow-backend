<?php

namespace App\Http\Controllers\API;

use App\Models\Reply;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReplyControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mendapatkan data Reply terbaru
        $replies = Reply::latest()->get();

        return response()->json([
            'message' => 'Data Reply',
            'status' => 'success',
            'data' => $replies
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'comment_id' => 'required|exists:comments,id',
            'content' => 'required|string|max:255',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'published' => 'nullable|boolean',
        ], [
            'user_id.required' => 'User ID harus diisi',
            'user_id.exists' => 'User ID tidak valid',
            'comment_id.required' => 'Comment ID harus diisi',
            'comment_id.exists' => 'Comment ID tidak valid',
            'content.required' => 'Konten harus diisi',
            'content.max' => 'Konten terlalu panjang',
            'image.mimes' => 'Format gambar tidak valid',
            'image.max' => 'Ukuran gambar terlalu besar',
        ]);
        // Jika tidak ada data published yang dikirim, maka set published ke true
        if (!isset($validatedData['published'])) {
            $validatedData['published'] = true;
        }
           // Log data validasi untuk debugging
        Log::info('BlogPost validation successful', $validatedData);
        
        // Simpan data ke dalam database
        $reply = Reply::create($validatedData);

        return response()->json([
            'message' => 'Reply berhasil disimpan',
            'status' => 'success',
            'data' => $reply
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // 
        $reply = Reply::find($id);
        if (!$reply) {
            return response()->json([
                'message' => 'Reply tidak ditemukan',
                'status' => 'error'
            ], 404);
        }
        return response()->json([
            'message' => 'Data Reply',
            'status' => 'success',
            'data' => $reply
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(string $id)
    // {
    //     //

    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $reply = Reply::find($id);
        if (!$reply) {
            return response()->json([
                'message' => 'Reply tidak ditemukan',
                'status' => 'error'
            ], 404);
        }
        $validatedData = $request->validate(['content' => 'nullable|string']);
        $reply->update($validatedData);

        return response()->json(['status' => 'sukses', 'pesan' => 'Komentar berhasil diperbarui', 'data' => $reply]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $reply = Reply::find($id);
        if (!$reply) {
            return response()->json([
                'message' => 'Reply tidak ditemukan',
                'status' => 'error'
            ], 404);
        }
        $reply->delete();
        return response()->json([
            'message' => 'Reply berhasil dihapus',
            'status' => 'success'
        ]);
    }
    
}
