<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentControllerApi extends Controller
{
    // Mengambil semua komentar
    public function index()
    {
        $comments = Comment::all();
        return response()->json([
            'status' => 'sukses',
            'data' => $comments,
        ]);
    }

    // Menyimpan komentar baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'post_id' => 'required|integer',
            'user_id' => 'required|integer',
            'content' => 'required|string',
        ]);

        try {
            $comment = Comment::create($validatedData);
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Komentar berhasil dibuat',
                'data' => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal membuat komentar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mengambil komentar spesifik
    public function show($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Komentar tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'sukses', 'data' => $comment]);
    }

    // Memperbarui komentar
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Komentar tidak ditemukan'], 404);
        }

        $validatedData = $request->validate(['content' => 'nullable|string']);
        $comment->update($validatedData);

        return response()->json(['status' => 'sukses', 'pesan' => 'Komentar berhasil diperbarui', 'data' => $comment]);
    }

    // Menghapus komentar
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Komentar tidak ditemukan'], 404);
        }

        $comment->delete();
        return response()->json(['status' => 'sukses', 'pesan' => 'Komentar berhasil dihapus']);
    }
}
