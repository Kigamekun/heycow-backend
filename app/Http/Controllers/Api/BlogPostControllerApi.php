<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogPostControllerApi extends Controller
{
    public function index()
    {
        return BlogPost::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|bigint',
            'content' => 'required|string',
        ]);

        $blogPost = BlogPost::create($validatedData);
        return response()->json($blogPost, 201);
    }

    public function show($id)
    {
        $blogPost = BlogPost::findOrFail($id);
        return response()->json($blogPost);
    }

    public function update(Request $request, $id)
    {
        $blogPost = BlogPost::findOrFail($id);
        $validatedData = $request->validate([
            'content' => 'string|nullable',
        ]);

        $blogPost->update($validatedData);
        return response()->json($blogPost);
    }

    public function destroy($id)
    {
        $blogPost = BlogPost::findOrFail($id);
        $blogPost->delete();
        return response()->json(null, 204);
    }
}
