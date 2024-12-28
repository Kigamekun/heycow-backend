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

        $blogPosts = $query->orderBy('created_at')->paginate($perPage);

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
        // Validasi data
        $validatedData = $request->validate([
            'user_id' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'category' => 'nullable|string|in:forum,jual',
            'price' => 'nullable|integer|required_if:category,jual',
            'cattle_id' => 'nullable|exists:cattle,id|required_if:category,jual',
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

        // Menangani 'published_at' jika tidak ada
        if (empty($validatedData['published_at'])) {
            $validatedData['published_at'] = now();
        }

        // Menyimpan user id yang sedang login
        $user = Auth::id();

        Log::info('BlogPost validation successful', $validatedData);

        // Mengambil cattle jika kategori adalah 'jual' dan user memiliki cattle
        $cattle = null;
        if ($validatedData['category'] === 'jual') {
            $cattle = Cattle::where('user_id', $user)->first();
        }

        // Jika kategori adalah 'forum', set cattle_id dan price menjadi null
        if ($validatedData['category'] === 'forum') {
            $validatedData['cattle_id'] = null;
            $validatedData['price'] = null;
        }

        // Membuat BlogPost
        $blogPost = BlogPost::create([
            'user_id' => $user,
            'title' => $validatedData['title'],
            'content' => $validatedData['content'],
            'cattle_id' => $validatedData['cattle_id'],
            'image' => $request->file('image') ? $request->file('image')->store('blog_images', 'public') : null,
            'price' => $validatedData['price'] ?? null,
            'category' => $validatedData['category'],
            'published_at' => $validatedData['published_at'],
        ]);

        // Menyusun response jika berhasil
        return response()->json([
            'message' => 'BlogPost berhasil ditambahkan',
            'status' => 'success',
            'statusCode' => 200,
            'data' => $blogPost
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Menangani error validasi
        return response()->json([
            'message' => 'Validasi gagal',
            'errors' => $e->errors(),
            'status' => 'error'
        ], 422);
    } catch (\Exception $e) {
        // Menangani error lainnya
        return response()->json([
            'message' => 'Terjadi kesalahan saat menyimpan data',
            'status' => 'error',
            'error' => $e->getMessage(),
        ], 500);
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
        // Validasi data berdasarkan kategori
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'category' => 'nullable|string|in:forum,jual',
            'price' => 'nullable|integer|required_if:category,jual',
            'cattle_id' => 'nullable|exists:cattle,id|required_if:category,jual',
            'published' => 'nullable|string|in:draft,published',
            'published_at' => 'nullable|date',
        ], [
            'title.string' => 'Judul harus berupa teks',
            'content.string' => 'Konten harus berupa teks',
            'price.integer' => 'Harga harus berupa angka',
            'cattle_id.exists' => 'Cattle ID tidak valid',
        ]);

        // Pastikan published_at memiliki nilai yang valid
        $publishedAt = $validatedData['published_at'] ?? $blogPost->published_at;

        // Jika published_at berupa string yang bisa diparse (misalnya '15 minutes ago'), ubah menjadi waktu yang valid
        if (is_string($publishedAt)) {
            $publishedAt = \Carbon\Carbon::parse($publishedAt)->format('Y-m-d H:i:s');
        }

        // Memastikan 'category' ada dalam validatedData
        $category = $validatedData['category'] ?? $blogPost->category;

        // Jika kategori adalah 'forum', set cattle_id dan price menjadi null
        if ($category === 'forum') {
            $validatedData['cattle_id'] = null;
            $validatedData['price'] = null;
        }

        // Update blog post
        $blogPost->update([
            'title' => $validatedData['title'] ?? $blogPost->title,
            'content' => $validatedData['content'] ?? $blogPost->content,
            'image' => $request->file('image') ? $request->file('image')->store('blog_images', 'public') : $blogPost->image,
            'category' => $category,
            'price' => $validatedData['price'] ?? $blogPost->price,
            'cattle_id' => $validatedData['cattle_id'] ?? $blogPost->cattle_id,
            'published_at' => $publishedAt, // Menyimpan tanggal yang valid
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
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan saat memperbarui data',
            'status' => 'error',
            'error' => $e->getMessage(),
        ], 500);
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
