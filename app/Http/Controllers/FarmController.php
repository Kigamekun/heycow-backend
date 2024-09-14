<?php

namespace App\Http\Controllers;

use App\Models\{Farm, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use MongoDB\BSON\ObjectId;
class FarmController extends Controller
{

    public function index(Request $request)
    {

        $data = Farm::latest()->get();
        // dd($data);

        if ($request->ajax()) {

            $data = Farm::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {

                    $id = Crypt::encrypt($row->_id);
                    $btn = '<div class="d-flex" style="gap:5px;">';
                    $btn .= '
                    <button type="button" title="EDIT" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateData"
                    data-name="' . $row->name . '"
                    data-location="' . $row->location . '"
                    data-owner_id="' . (string) $row->owner_id . '"

                    data-url="' . route('farm.update', ['id' => $id]) . '"
                    >
                        Edit
                    </button>';
                    $btn .= '
                    <form id="deleteForm" action="' . route('farm.delete', ['id' => $id]) . '" method="POST">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                                <button type="button" title="DELETE" class="btn btn-sm btn-danger btn-delete" onclick="confirmDelete(event)">
                                    Delete
                                </button>
                            </form>
                    </div>';
                    return $btn;
                })

                ->addColumn('image', function ($row) {
                    if ($row->image != null) {
                        $image = '<img src="' . asset('storage/farm/' . $row->image) . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                    } else {
                        $image = '<img src="' . url('assets/img/noimage.jpg') . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                    }

                    return $image;
                })

                ->rawColumns(['action', 'image'])
                ->make(true);
        }

        return view('admin.farm', [
            'data' => Farm::all(),
            'owners' => User::all()
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'owner_id' => 'required',
        ]);

        Farm::create([
            'name' => $request->name,
            'location' => $request->location,
            'owner_id' => $request->owner_id,
        ]);

        return redirect()->back()->with(['message' => 'Farm berhasil ditambahkan', 'status' => 'success']);
    }



    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $farm = Farm::where('_id', new ObjectId($id))->first();


        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'owner_id' => 'required',
        ]);

        $farm->update([
            'name' => $request->name,
            'location' => $request->location,
            'owner_id' => new ObjectId($request->owner_id),
        ]);

        return redirect()->route('farm.index')->with(['message' => 'Farm berhasil di update', 'status' => 'success']);

    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        Farm::where('_id', new ObjectId($id))->delete();
        return redirect()->route('farm.index')->with(['message' => 'Farm berhasil di delete', 'status' => 'success']);
    }
}
