<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use \Illuminate\Support\Facades\Auth;
use function Pest\Laravel\get;
use Illuminate\Support\Facades\Storage;

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
                    $rowBlogPost->published . '"
                    data-user_id="' . (string) $rowBlogPost->user_id . '"
                    data-url="' . route('blog.update', ['id' => $id]) . '"
                    >
                        Edit
                    </button>';
                    $btn .= '
                    <form id="deleteForm" action="' . route('blog.delete', ['id' => $id]) . '" method="POST">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                                <button type="button" title="DELETE" class="btn btn-sm btn-danger btn-delete" onclick="confirmDelete(event)">
                                    Delete
                                </button>
                            </form>
                    <button type="button" title="DETAIL" class="btn btn-sm btn-success btn-delete">
                        Detail
                    </button>
                    </div>';
                    return $btn;
            })
            ->addColumn('image', function ($rowBlogPost) {
                if ($rowBlogPost->image != null) {
                    $image = '<img src="' . asset('storage/' . $rowBlogPost->image) . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                } else {
                    $image = '<img src="' . url('assets/img/noimage.jpg') . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                }
                return $image;
            })
            
            ->addColumn('owner', function ($rowBlogPost) {
                return;
            })
            ->rawColumns(['action', 'image', 'button'])
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
        
        //ini sebgai FUNCTION UPDATE Untuk mendapatkan para data
        if (Auth::user()->role == 'admin') {
            $request->validate([
                "title" => "required",
                "content" => "required",
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'published' => 'required',
                'user_id' => 'required|exists:users,id',
            ]);
            BlogPost::create([
            
                'title' => $request->title,
                'content' => $request->input('content'),
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                // 'name' => $request -> name,
                'published'=> $request -> published, // Buat status publish
                'user_id' => $request->user_id // No need for 'new' here
            ]);
        } else {
            $request->validate([
                
                "title" => "required",
                "content" => "required",
                "image" => "required",
                'published' => 'required',
            ]);
            BlogPost::create([
            
                'title' => $request->title,
                'content' => $request->input('content'),
                'image' => $request -> image,
                // 'name' => $request -> name,
                'published'=> $request -> published, // Buat status publish
                'user_id' => Auth::id() // No need for 'new' here
            ]);
        }
        // dd($request->user_id);
        
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
        $id = Crypt::decrypt($id);

        // Find the farm by ID
        $blogPost = BlogPost::where('id', $id)->first();

        if (\Illuminate\Support\Facades\Auth::user()->role == 'admin') {$request->validate([
            "tilte" => "required",
            "content" => "required",
            "image" => "required",
            // "name" => "required",
            'published' => 'published', 
            "user_id"=> "required" // Ensure user_id exists in the users table
        ]);}
        else{
            $request->validate([
                "tilte" => "required",
                "content" => "required",
                "image" => "required",
                // "name" => "required",
                'published' => 'published', 
                // "user_id"=> "required" // Ensure user_id exists in the users table
            ]);
        };
        $imageset = $blogPost->image;
        if($request->hasFile('image')){
            if($imageset){
                Storage::disk('public')->delete($imageset);
            }

            $images = $request->file('image');
            $imageset = $images->store('blogimages','public');
        };
        // Update the farm information
        $blogPost->update([
            'title' => $request->title,
            'content' => $request->input('content'),
            'published'=> $request -> published,  
            'image' => $request->image,// Buat status publish
            'user_id' => $request->user_id// No need for 'new' here
        ]);

        // Redirect back to the farm index with a success message
        return redirect()->route('blog.index')->with(['message' => 'Postingan berhasil di update', 'status' => 'success']);
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
}
