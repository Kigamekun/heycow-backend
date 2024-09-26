<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogPostControllerApi extends Controller
{
    public function index()
    {
        // Mendapatkan data BlogPost terbaru
        $blogPosts = BlogPost::latest()->get();

        return response()->json([
            'message' => 'Data BlogPost',
            'status' => 'success',
            'data' => $blogPosts
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ], [
                'user_id.required' => 'User ID harus diisi',
                'user_id.exists' => 'User ID tidak valid',
                'title.required' => 'Judul harus diisi',
                'content.required' => 'Konten harus diisi',
            ]);

            // Log data validasi untuk debugging
            Log::info('BlogPost validation successful', $validatedData);

            // Simpan blog post
            $blogPost = BlogPost::create($validatedData);

            return response()->json([
                'message' => 'BlogPost berhasil ditambahkan',
                'status' => 'success',
                'data' => $blogPost
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
        $blogPost = BlogPost::find($id);

        if (!$blogPost) {
            return response()->json([
                'message' => 'BlogPost tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        return response()->json([
            'message' => 'Data BlogPost ditemukan',
            'status' => 'success',
            'data' => $blogPost
        ]);
    }

    public function update(Request $request, $id)
    {
        $blogPost = BlogPost::find($id);

        if (!$blogPost) {
            return response()->json([
                'message' => 'BlogPost tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        try {
            // Validasi data input
            $validatedData = $request->validate([
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
            ], [
                'title.string' => 'Judul harus berupa teks',
                'content.string' => 'Konten harus berupa teks',
            ]);

            // Update blog post jika validasi berhasil
            $blogPost->update($validatedData);

            return response()->json([
                'message' => 'BlogPost berhasil diupdate',
                'status' => 'success',
                'data' => $blogPost
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
        $blogPost = BlogPost::find($id);

        if (!$blogPost) {
            return response()->json([
                'message' => 'BlogPost tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $blogPost->delete();

        return response()->json([
            'message' => 'BlogPost berhasil dihapus',
            'status' => 'success'
        ], 200);
    }
}
