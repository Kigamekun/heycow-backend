<?php

namespace App\Http\Controllers\Api;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Like;

class LikeControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
{
    $blogPost = BlogPost::find($id);

    if (!$blogPost) {
        return response()->json([
            'status' => 'error',
            'message' => 'Blog post tidak ditemukan',
        ], 404);
    }

    $likes = $blogPost->likes()->latest()->get();
    $likeCount = $likes->count(); // Menghitung jumlah like

    return response()->json([
        'status' => 'sukses',
        'data' => [
            'likes' => $likes,
            'like_count' => $likeCount, // Menambahkan jumlah like
        ],
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    // 

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
{
    $validatedData = $request->validate([
        'like' => 'required|string|in:like,dislike',
    ]);

    // Cek apakah pengguna sudah memberi like pada post
    $existingLike = Like::where('post_id', $id)
        ->where('user_id', auth()->user()->id)
        ->first();

    try {
        if ($existingLike) {
            // Update existing like
            $existingLike->update(['like' => $validatedData['like']]);
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Like berhasil diperbarui',
                'data' => $existingLike,
            ], 200);
        } else {
            // Buat like baru
            $like = Like::create([
                'like' => $validatedData['like'],
                'user_id' => auth()->user()->id,
                'post_id' => $id,
            ]);
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Like berhasil dibuat',
                'data' => $like,
            ], 201);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'gagal',
            'pesan' => 'Gagal membuat like',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        //
        $like = Like::find($id);
        if (!$like) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Like tidak ditemukan'], 404);
        }
        return response()->json(['status' => 'sukses', 'data' => $like]);
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
    public function update(Request $request,  $id)
    {
        // //
        // $like = Like::find('post_id', $id);
        
        // $validatedData = $request->validate([
        //     'like' => 'required|string|in:like,dislike',
        // ]);
        // $like->update($validatedData);

        // return response()->json(['status' => 'sukses', 'pesan' => 'Like berhasil diperbarui', 'data' => $like]);

        $like = Like::find($id);
        if (!$like) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Like tidak ditemukan'], 404);
        }
        
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        //
        $like = Like::find($id);
        $like->delete();
        return response()->json(['status' => 'sukses', 'pesan' => 'Like berhasil dihapus']);
    }
}
