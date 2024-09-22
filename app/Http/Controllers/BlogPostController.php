<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\User;
use App\Models\Post; // Add this line to import the Post model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use \Illuminate\Support\Facades\Auth;
use function Pest\Laravel\get;
use Illuminate\Support\Facades\Storage;
// Removed incorrect use statement for authorize()
class BlogPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        # ini sebgai GET Untuk mendapatkan para data
        $data = BlogPost::latest()->get();
        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($rowBlogPost) {
                    $id = Crypt::encrypt($rowBlogPost->id);
                    $file_image = url('storage/' . $rowBlogPost->image);


                    $btn = '<div class="d-flex" style="gap:5px;">';
                    $btn .= '<button type="button" title="EDIT" class="btn  text-white btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateData"
                        data-id="' . $id . '"
                        data-url="' . route('blog.update', ['id' => $id]) . '">
                    Edit
                </button>';

                    if ($rowBlogPost->published == 1) {
                        $btn .= '<form id="unpublishForm" action="' . route('blog.unpublish', ['id' => $id]) . '" method="POST">
                                ' . csrf_field() . '
                                <input type="hidden" name="_method" value="PATCH">
                                <button type="submit" title="UNPUBLISH" class="btn btn-sm btn-danger btn-delete" onclick="confirmUnpublish(event)">
                                    Unpublish
                                </button>
                            </form>';
                    } else {
                        $btn .= '<form id="publishForm" action="' . route('blog.publish', ['id' => $id]) . '" method="POST">
                                ' . csrf_field() . '
                                <input type="hidden" name="_method" value="PATCH">
                                <button type="submit" title="PUBLISH" class="btn btn-sm btn-success btn-delete" onclick="confirmPublish(event)">
                                    Publish
                                </button>
                            </form>';
                    }



                    $btn .= '
                    <form id="deleteForm" action="' . route('blog.delete', ['id' => $id]) . '" method="POST">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                                <button type="button" title="DELETE" class="btn btn-sm btn-danger btn-delete" onclick="confirmDelete(event)">
                                    Delete
                                </button>
                            </form>
                    <button type="button" title="DETAIL" class="btn btn-sm btn-info text-white btn-delete" onclick="location.href=">
                        See Comments
                    </button>
                    </div>';
                    return $btn;
                })
                ->addColumn('image', function ($rowBlogPost) {
                    if ($rowBlogPost->image != null) {
                        $image = '<img src="' . url('storage/' . $rowBlogPost->image) . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                    } else {
                        $image = '<img src="' . url('assets/img/noimage.jpg') . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                    }
                    return $image;
                })

                ->addColumn('owner', function ($rowBlogPost) {
                    return $rowBlogPost->owner->name;
                })
                ->editColumn('published', function ($rowBlogPost) {
                    if ($rowBlogPost->published == 1) {
                        return '<span class="badge bg-success">Published</span>';
                    } else {
                        return '<span class="badge bg-danger">Draft</span>';
                    }
                })
                ->rawColumns(['action', 'published', 'image', 'button'])
                ->make(true);
        }
        return view('admin.blog', [
            'data' => BlogPost::all(),
            'owners' => User::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            "title" => "required",
            "content" => "required",
            "image" => "nullable|image",
            'published' => 'required|in:1,0',
        ];

        if (Auth::user()->role == 'admin') {
            $rules["user_id"] = "required|exists:users,id"; // Only admin can update user_id
        }

        $request->validate($rules);

        $imageset = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageset = $image->store('blogimages', 'public'); // Store the new image
        }


        BlogPost::create([
            'title' => $request->title,
            'content' => $request->input('content'),
            'image' => $imageset,
            'status' => $request->pzublished,
            'user_id' => $request->user_id ?? Auth::id()
        ]);

        return redirect()->route('blog.index')->with(['message' => 'Postingan berhasil di publish', 'status' => 'success']);
    }

    public function detail($id)
    {
        $id = Crypt::decrypt($id);
        $blogPost = BlogPost::findOrFail($id);
        return response()->json(['post'=> $blogPost,'owners' => User::all()]);
    }

    public function update(Request $request, $id)
    {
        $decryptedId = Crypt::decrypt($id);

        // Find the blog post by ID
        $blogPost = BlogPost::findOrFail($decryptedId);
        // Define validation rules
        $rules = [
            "title" => "required",
            "content" => "required",
            "image" => "nullable|image",
            'published' => 'required|in:published,draft',
        ];

        if (Auth::user()->role == 'admin') {
            $rules["user_id"] = "required|exists:users,id"; // Only admin can update user_id
        }

        $request->validate($rules);

        // Check if the user is authorized to update
        if (Auth::user()->role != 'admin' && Auth::user()->id != $blogPost->user_id) {
            return redirect()->route('blog.index')->with(['message' => 'Unauthorized action', 'status' => 'error']);
        }

        // Handle image upload
        $imageset = $blogPost->image;
        if ($request->hasFile('image')) {
            if ($imageset) {
                Storage::disk('public')->delete($imageset); // Delete the old image
            }

            $image = $request->file('image');
            $imageset = $image->store('blogimages', 'public'); // Store the new image
        }

        // Update the blog post
        $blogPost->update([
            'title' => $request->title,
            'content' => $request->input('content'),
            'image' => $imageset,
            'status' => $request->published,
            'user_id' => $request->user_id ?? $blogPost->user_id
        ]);

        // Redirect back to the blog index with a success message
        return redirect()->route('blog.index')->with(['message' => 'Postingan berhasil di update', 'status' => 'success']);
    }
    // Logs the value of 'published' field


    public function comments()
    {

    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        # ini sebgai Delete Untuk mendapatkan para data
        $id = Crypt::decrypt($id);

        // Delete the farm with the decrypted ID
        BlogPost::where('id', $id)->delete();

        // Redirect with a success message
        return redirect()->route('blog.index')->with(['message' => 'Farm berhasil di delete', 'status' => 'success']);
    }


    public function publish($id)
    {
        $id = Crypt::decrypt($id);
        $blogPost = BlogPost::findOrFail($id);
        $blogPost->update([
            'published' => 1
        ]);
        return redirect()->route('blog.index')->with(['message' => 'Postingan berhasil di publish', 'status' => 'success']);
    }

    public function unpublish($id)
    {
        $id = Crypt::decrypt($id);
        $blogPost = BlogPost::findOrFail($id);
        $blogPost->update([
            'published' => 0
        ]);
        return redirect()->route('blog.index')->with(['message' => 'Postingan berhasil di unpublish', 'status' => 'success']);
    }
}
