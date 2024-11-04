<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\post;

class CommentControllerApi extends Controller
{
    public function index($id)
    {
        $blogPost = BlogPost::find($id);

        if (!$blogPost) {
            return response()->json([
                'status' => 'error',
                'message' => 'Blog post tidak ditemukan',
            ], 404);
        }

        $comments = $blogPost->comments()->latest()->get();
        $commentCount = $comments->count(); // Menghitung jumlah komentar

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'comments' => $comments,
                'comment_count' => $commentCount, // Menambahkan jumlah komentar
            ],
        ]);
    }


    // Menyimpan komentar baru
    public function store(Request $request, $id)
    {
        $validatedData = $request->validate([
            // 'post_id' => 'integer',
            // 'user_id' => 'required|integer',
            'content' => 'required|string',
        ]);
        
        try {
            $comment = Comment::create([
                'content' => $validatedData['content'],
                'user_id' => auth()->user()->id,
                'post_id' => $id,
            ]);
  
        // Hapus atau komentar baris ini
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
        // $comment = Comment::find($id);
        $comment = Comment::with('replies')->find($id);
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

        $validatedData = $request->validate([
            'content' => 'nullable|string',
            'like' => 'nullable|String|in:like,dislike'
        ]);
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
