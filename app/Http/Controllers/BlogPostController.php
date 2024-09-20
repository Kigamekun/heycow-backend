<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class BlogPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        # ini sebgai GET Untuk mendapatkan para data
        $data = BlogPost::latest()->get();
        if ($request->ajax()){
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($rowBlogPost) {
                $id = Crypt::encrypt($rowBlogPost->id);
                $btn = '<div class="d-flex" style="gap:5px;">';
                $btn .= '
                    <button type="button" title="EDIT" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateData"
                    data-name="' . $rowBlogPost->title . '"
                    data-address="' . 
                    $rowBlogPost->content . '"
                    data-address="' . 
                    $rowBlogPost->image . '"
                    data-address="'.
                    $rowBlogPost->name . '"
                    data-user_id="' . (string) $rowBlogPost->user . '"
                    data-url="' . route('blogpost.update', ['id' => $id]) . '"
                    >
                        Edit
                    </button>';
                    $btn .= '
                    <form id="deleteForm" action="' . route('blogpost.delete', ['id' => $id]) . '" method="POST">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                                <button type="button" title="DELETE" class="btn btn-sm btn-danger btn-delete" onclick="confirmDelete(event)">
                                    Delete
                                </button>
                            </form>
                    </div>';
                    return $btn;
            })
            ->addColumn('image', function ($rowBlogPost) {
                if ($rowBlogPost->image != null) {
                    $image = '<img src="' . asset('storage/post/' . $rowBlogPost->image) . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                } else {
                    $image = '<img src="' . url('assets/img/noimage.jpg') . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                }
                return $image;
            })
            ->addColumn('owner', function ($rowBlogPost) {
                return $rowBlogPost>owner->name;
            })
            ->rawColumns(['action', 'image'])
            ->make(true);
        }
        return view('admin.blog',[
            'data'=> BlogPost::all(),
            'owners' => User::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    
    // public function create()
    // {
    //     # ini sebgai POST Untuk mendapatkan para data
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        # ini sebgai FUNCTION UPDATE Untuk mendapatkan para data
        $request -> validate([
            #Validaate title Required 
            "tilte" => "required",
            "content" => "required",
            "image" => "required",
            "name" => "required", 
            "user_id"=> "required"
        ]);

        BlogPost::create([
            # Input
            "title" => $request -> title,
            "content" => $request->input('content'),
            'image'=> $request -> image,
            "name" => $request -> name,
            "user_id"=> $request -> user_id
        ]);
        // Untuk cekout apakah sudah publish atau belum
        return redirect()->back()->with(['message' => 'Postingan sudah di publish', 'status' => 'success']);
        //
    }

    /**
     * Display the specified resource.
     */
    // public function show(BlogPost $blogPost)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(BlogPost $blogPost)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BlogPost $blogPost, $id)
    {
        # ini sebgai Update Untuk mendapatkan para data
            
        // Decrypt the ID
        $id = Crypt::decrypt($id);

        // Find the farm by ID
        $blogPost = BlogPost::where('id', $id)->first();

        // Validate the request data
        $request->validate([
            "tilte" => "required",
            "content" => "required",
            "image" => "required",
            "name" => "required", 
            "user_id"=> "required" // Ensure user_id exists in the users table
        ]);

        // Update the farm information
        $blogPost->update([
            'title' => $request->title,
            'content' => $request->input('content'),
            'image' => $request -> image,
            'name' => $request -> name,
            'user_id' => $request->user_id,  // No need for 'new' here
        ]);

        // Redirect back to the farm index with a success message
        return redirect()->route('blog.index')->with(['message' => 'Postingan berhasil di update', 'status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogPost $blogPost)
    {
        # ini sebgai Delete Untuk mendapatkan para data
        //
    }
}
