<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Cattle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogPostControllerApi extends Controller
{
    
    public function index(Request $request)
    {
        $blogPosts = BlogPost::with(['comments', 'likes', 'cattle'])
            ->get()
            ->makeHidden(['user_id', 'cattle_id']);

        $sortBy = $request->query('sort_by', 'created_at'); // Default sorting by 'created_at'
        $sortOrder = $request->query('sort_order', 'desc'); // Default sorting order 'desc'
        $perPage = $request->query('per_page', 10); // Default items per page
        $search = $request->query('search', ''); // Default search query

        // Validasi parameter sorting
        $allowedSortBy = ['created_at', 'title', 'published_at']; // Kolom yang diizinkan untuk sorting
        
        if (!in_array($sortBy, $allowedSortBy)) {
            return response()->json([
            'message' => 'Kolom sorting tidak valid',
            'status' => 'error'
            ], 400);
        }

        $allowedSortOrder = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrder)) {
            return response()->json([
            'message' => 'Arah sorting tidak valid',
            'status' => 'error'
            ], 400);
        }

        // Ambil data BlogPost dengan sorting, pagination, dan pencarian
        $blogPosts = BlogPost::where(function($query) use ($search) {
            if ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            }
        })
        ->orderBy($sortBy, $sortOrder)
        ->paginate($perPage);


        // Kita ambil category menggunakan params yakni forum atau jual
       // forum
        if ($request->query('category') == 'forum') {
            $blogPosts = BlogPost::where('category', 'forum')->get();
        }
        // jual
        if ($request->query('category') == 'jual') {
            $blogPosts = BlogPost::where('category', 'jual')->get();
        }
        // $blogPosts = BlogPost::latest()->get();

        return response()->json([
            'message' => 'Data BlogPost',
            'status' => 'success',
            'data' => $blogPosts
        ]);
    }

    public function store(Request $request)
    {
        try {
            // $request->validate([
            //     'image' => 'nullable|mimes:png,jpg,jpeg',
            // ]);
            // Validasi input

            
            $validatedData = $request->validate([
                'user_id' => 'exists:users,id',
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
                // 'cattle_id' => 'nullable|exists:cattles,id',
                'category' => 'nullable|string|in:forum,jual',
                'published' => 'nullable|string|in:draft,published',
                "published_at" => "nullable|date"
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
            
            // $cattle_id = $cattle ? $cattle->id : null;
            
            // Log data validasi untuk debugging
            Log::info('BlogPost validation successful', $validatedData);
            
            $cattle = Cattle::where('user_id', $user)->first();

            // Simpan blog post
            $blogPost = BlogPost::create([
                'user_id' => $user,
            'title' => $validatedData['title'],
            'content' => $validatedData['content'],
            'image' => $request->file('image') ? $request->file('image')->store('blog_images', 'public') : null,
            // 'cattle_id' => $cattle->id,
            'category' => $validatedData['category'],
            // 'published' => $validatedData['published'],
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
        // $blogPost = BlogPost::find($id);
        
        // $blogPost = BlogPost::with('comments')->find($id);
        $blogPost = BlogPost::with('comments', 'likes')->find($id);
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
                // 'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            ], [
                'title.string' => 'Judul harus berupa teks',
                'content.string' => 'Konten harus berupa teks',
            ]);

            // Update blog post jika validasi berhasil
            $blogPost->update([
                'title' => $validatedData['title'] ?? $blogPost->title,
                'content' => $validatedData['content'] ?? $blogPost->content,
                // 'image' => $request->file('image') ? $request->file('image')->store('blog_images', 'public') : $blogPost->image,
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
        // $blogPost = BlogPost::with('comments')->find($id);

        // if ($blogPost) {
        //     $blogPost->comments()->delete();
        // }
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
