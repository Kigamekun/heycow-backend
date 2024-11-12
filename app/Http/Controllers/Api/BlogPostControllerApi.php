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
    private function formatPrice($price)
    {
        return 'Rp ' . number_format($price, 0, '', '.');
    }

    public function index(Request $request)
    {
        $user = Auth::id();

        $query = BlogPost::with(['comments', 'likes', 'cattle'])
            ->withCount(['comments', 'likes']);

        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search', '');
        $category = $request->query('category', '');
        $allowedSortBy = ['created_at', 'title', 'published_at'];
        $allowedSortOrder = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSortBy)) {
            return response()->json([
                'message' => 'Kolom sorting tidak valid',
                'status' => 'error'
            ], 400);
        }

        if (!in_array($sortOrder, $allowedSortOrder)) {
            return response()->json([
                'message' => 'Arah sorting tidak valid',
                'status' => 'error'
            ], 400);
        }

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($category) {
            $query->where('category', $category);
        }

        $blogPosts = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $user = auth()->user(); // atau cara lain untuk mendapatkan user yang sedang login
        $blogPosts->getCollection()->transform(function ($post) use ($user) {
            $post->user = $post->user->name;
            $post->published_at = $post->published_at
                ? Carbon::parse($post->published_at)->diffForHumans()
                : null;
            $post->price = $post->price ? $this->formatPrice($post->price) : null;
            $post->isLiked = $post->likes->contains('user_id', $user->id); // Pastikan $user->id adalah ID yang benar
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
                'price' => 'nullable|integer',
                'cattle_id' => 'nullable|exists:cattle,id',
                'published' => 'nullable|string|in:draft,published',
                'published_at' => 'nullable|date',
            ], [
                'user_id.required' => 'User ID harus diisi',
                'cattle_id.exists' => 'Cattle ID tidak valid',
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

            Log::info('BlogPost validation successful', $validatedData);

            $cattle = Cattle::where('user_id', $user)->first();

            $blogPost = BlogPost::create([
                'user_id' => $user,
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'cattle_id'=>$validatedData['cattle_id'],
                // 'cattle_id' => $cattle ? $cattle->id : null,
                'image' => $request->file('image') ? $request->file('image')->store('blog_images', 'public') : null,
                'price' => $validatedData['price'] ?? null,
                'category' => $validatedData['category'],
                'published_at' => $validatedData['published_at']
            ]);

            return response()->json([
                'message' => 'BlogPost berhasil ditambahkan',
                'status' => 'success',
                'statusCode' => 200,
                'data' => $blogPost
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }


    public function show($id)
    {
        $blogPost = BlogPost::with(['comments', 'likes', 'user', 'cattle'])
            ->withCount(['comments', 'likes'])
            ->find($id);

        if (!$blogPost) {
            return response()->json([
                'message' => 'BlogPost tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $user = Auth::id();
        $isLiked = $blogPost->likes->contains('user_id', $user);

        $blogPost->price = $blogPost->price ? $this->formatPrice($blogPost->price) : null;
        $blogPost->isLiked = $isLiked;

        return response()->json([
            'message' => 'Data BlogPost ditemukan',
            'status' => 'success',
            'data' => $blogPost,
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
            $validatedData = $request->validate([
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
                'price' => 'nullable|integer',
            ], [
                'title.string' => 'Judul harus berupa teks',
                'content.string' => 'Konten harus berupa teks',
            ]);

            $blogPost->update([
                'title' => $validatedData['title'] ?? $blogPost->title,
                'content' => $validatedData['content'] ?? $blogPost->content,
                'image' => $request->file('image') ? $request->file('image')->store('blog_images', 'public') : $blogPost->image,
                'price' => $validatedData['price'] ?? $blogPost->price,
            ]);

            return response()->json([
                'message' => 'BlogPost berhasil diupdate',
                'status' => 'success',
                'data' => $blogPost
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
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
