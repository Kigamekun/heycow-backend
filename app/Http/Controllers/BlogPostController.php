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
        if ($request->ajax()){
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($rowBlogPost) {
                $id = Crypt::encrypt($rowBlogPost->id);
                $btn = '<div class="d-flex" style="gap:5px;">';
                // $btn .= '
                //     <button type="button" title="EDIT" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateData"
                //     data-name="' . $rowBlogPost->title . '"
                //     data-address="' . 
                //     $rowBlogPost->content . '"
                //     data-address="' . 
                //     $rowBlogPost->image . '"
                //     data-address="'.
                //     (boolean) $rowBlogPost->published . '"
                //     data-user_id="' . (string) $rowBlogPost->user_id . '"
                //     data-url="' . route('blog.update', ['id' => $id]) . '"
                //     >
                //         Edit
                //     </button>'
                    $btn .= '<button type="button" title="EDIT" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateData"
                    data-title="' . $rowBlogPost->title . '"
                    data-content="' . $rowBlogPost->content . '"
                    data-image="' . $rowBlogPost->image . '"
                    data-published="' . $rowBlogPost->published . '"
                    data-user_id="' . $rowBlogPost->user_id . '"
                    data-url="' . route('blog.update', ['id' => $id]) . '">
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
                    <button type="button" title="DETAIL" class="btn btn-sm btn-success btn-delete" onclick="location.href=">
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


    // public function update(Request $request, BlogPost $blogPost, $id)
    // {
    //     $id = Crypt::decrypt($id);

    //     // Find the farm by ID
    //     $blogPost = BlogPost::where('id', $id)->first();

    //     if (Auth::user()->role == 'admin') {$request->validate([
    //         "title" => "required",
    //         "content" => "required",
    //         "image" => "nullable|image",
    //         // "name" => "required",
    //         'published' => 'published|in:published,draft', 
    //         "user_id"=> "required" // Ensure user_id exists in the users table
    //     ]);}
    //     else{
    //         $request->validate([
    //             "title" => "required",
    //             "content" => "required",
    //             "image" => "nullable|image",
    //             // "name" => "required",
    //             'published' => 'required|in:published,draft', 
    //             // "user_id"=> "required" // Ensure user_id exists in the users table
    //         ]);
    //     };
    //     $imageset = $blogPost->image;
    //     // if($request->hasFile('image')){
    //     //     if($imageset){
    //     //         Storage::disk('public')->delete($imageset);
    //     //     }

    //     //     $images = $request->file('image');
    //     //     $imageset = $images->store('blogimages','public');
    //     // };
    //     // Update the farm information
    //     if ($request->hasFile('image')) {
    //         if ($imageset) {
    //             Storage::disk('public')->delete($imageset);
    //         }
        
    //         $image = $request->file('image');
    //         $imageset = $image->store('blogimages', 'public');
    //     }
        
    //     // Save the new image path if available
        
        
        
    //     $blogPost->update([
    //         'title' => $request->title,
    //         'content' => $request->input('content'),
    //         'image' => $imageset,
    //         'status' => $request->published,
    //         'user_id' => $request->user_id ?? $blogPost->user_id
    //     ]);
    //     // Redirect back to the farm index with a success message
    //     return redirect()->route('blog.index')->with(['message' => 'Postingan berhasil di update', 'status' => 'success']);
    // }

    public function update(Request $request, $id)
    {
        $decryptedId = Crypt::decrypt($id);
    
        // Find the blog post by ID
        $blogPost = BlogPost::findOrFail($decryptedId);
    
        
        $post = BlogPost::findOrFail($id);
        $this->authorize('update', $post);
        
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
            'status' => $request->published ,
            'user_id' => $request->user_id ?? $blogPost->user_id
        ]);
    
        // Redirect back to the blog index with a success message
        return redirect()->route('blog.index')->with(['message' => 'Postingan berhasil di update', 'status' => 'success']);
    }
// Logs the value of 'published' field
    

    public function comments(){
        
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
