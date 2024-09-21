<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentControllerApi extends Controller
{
    public function index()
    {
        return Comment::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'post_id' => 'required|bigint',
            'user_id' => 'required|bigint',
            'content' => 'required|string',
        ]);

        $comment = Comment::create($validatedData);
        return response()->json($comment, 201);
    }

    public function show($id)
    {
        $comment = Comment::findOrFail($id);
        return response()->json($comment);
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $validatedData = $request->validate([
            'content' => 'string|nullable',
        ]);

        $comment->update($validatedData);
        return response()->json($comment);
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return response()->json(null, 204);
    }
}
