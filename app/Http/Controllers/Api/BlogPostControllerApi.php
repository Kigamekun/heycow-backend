<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Cattle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BlogPostControllerApi extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::id();

        $query = BlogPost::where('user_id', $user)
            ->with(['comments', 'likes', 'cattle'])
            ->withCount(['comments', 'likes']);
        
            

        // Sorting, pagination, dan pencarian
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search', '');
        $category = $request->query('category', '');

        $allowedSortBy = ['created_at', 'title', 'published_at'];
        $allowedSortOrder = ['asc', 'desc'];

        // Validasi kolom sorting
        if (!in_array($sortBy, $allowedSortBy)) {
            return response()->json([
                'message' => 'Kolom sorting tidak valid',
                'status' => 'error'
            ], 400);
        }

        // Validasi arah sorting
        if (!in_array($sortOrder, $allowedSortOrder)) {
            return response()->json([
                'message' => 'Arah sorting tidak valid',
                'status' => 'error'
            ], 400);
        }

        if ($search) {
            $query->where(function($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $blogPosts = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        // Modify each blog post to have relative time for `published_at`
        $blogPosts->getCollection()->transform(function ($post) {
            $post->published_at = $post->published_at
                ? Carbon::parse($post->published_at)->diffForHumans()
                : null;

            return $post;
        });

        return response()->json([
            'message' => 'Data BlogPost',
            'status' => 'success',
            'data' => $blogPosts
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'exists:users,id',
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
                'category' => 'nullable|string|in:forum,jual',
                'published' => 'nullable|string|in:draft,published',
                'published_at' => 'nullable|date'
            ], [
                'user_id.required' => 'User ID harus diisi',
                'user_id.exists' => 'User ID tidak valid',
                'title.required' => 'Judul harus diisi',
                'content.required' => 'Konten harus diisi',
                'image.mimes' => 'Format gambar tidak valid',
                'image.max' => 'Ukuran gambar terlalu besar',
            ]);

            if (empty($validatedData['published_at'])) {
                $validatedData['published_at'] = now();
            }
            $user = Auth::id();

            // Log data validasi untuk debugging
            Log::info('BlogPost validation successful', $validatedData);

            $cattle = Cattle::where('user_id', $user)->first();

            // Simpan blog post
            $blogPost = BlogPost::create([
                'user_id' => $user,
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'image' => $request->file('image') ? $request->file('image')->store('blog_images', 'public') : null,
                'category' => $validatedData['category'],
                'published_at' => $validatedData['published_at']
            ]);

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
        $blogPost = BlogPost::with(['comments', 'likes'])
            ->withCount(['comments', 'likes']) // Menghitung jumlah komentar dan like
            ->find($id);

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
                'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            ], [
                'title.string' => 'Judul harus berupa teks',
                'content.string' => 'Konten harus berupa teks',
            ]);

            // Update blog post jika validasi berhasil
            $blogPost->update([
                'title' => $validatedData['title'] ?? $blogPost->title,
                'content' => $validatedData['content'] ?? $blogPost->content,
                'image' => $request->file('image') ? $request->file('image')->store('blog_images', 'public') : $blogPost->image,
            ]);

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
