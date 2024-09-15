<?php

namespace App\Http\Controllers;

use App\Models\Cattle;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use MongoDB\BSON\ObjectId;
class CattleController extends Controller
{
    public function index(Request $request)
    {
        $data = Cattle::latest()->get();
        if ($request->ajax()) {
            $data = Cattle::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $id = Crypt::encrypt($row->id);
                    $btn = '<div class="d-flex" style="gap:5px;">';
                    $btn .= '
                    <button type="button" title="EDIT" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateData"
                    data-name="' . $row->name . '"
                    data-breed="' . $row->breed . '"
                    data-status="' . $row->status . '"
                    data-birth_date="' . $row->birth_date . '"
                    data-birth_weight="' . $row->birth_weight . '"
                    data-farm_id="' . (string) $row->farm_id . '"
                    data-user_id="' . (string) $row->user_id . '"
                    data-iot_device_id="' . (string) $row->iot_device_id . '"
                    data-url="' . route('cattle.update', ['id' => $id]) . '"
                    >
                        Edit
                    </button>';
                    $btn .= '
                    <form id="deleteForm" action="' . route('cattle.delete', ['id' => $id]) . '" method="POST">
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
                        $image = '<img src="' . asset('storage/cattle/' . $row->image) . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                    } else {
                        $image = '<img src="' . url('assets/img/noimage.jpg') . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                    }
                    return $image;
                })
                ->rawColumns(['action', 'image'])
                ->make(true);
        }
        return view('admin.cattle', [
            'data' => Cattle::all(),
            'farms' => Farm::all(),
            'owners' => User::all(),
            'iot_devices' => IoTDevice::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'breed' => 'required',
            'status' => 'required|in:alive,dead,sold',
            'birth_date' => 'required|date',
            'birth_weight' => 'required|numeric',
            'farm_id' => 'required|exists:farms,id',
            'user_id' => 'required|exists:users,id',
            'iot_device_id' => 'required|exists:iot_devices,id',
        ]);

        Cattle::create([
            'name' => $request->name,
            'breed' => $request->breed,
            'status' => $request->status,
            'birth_date' => $request->birth_date,
            'birth_weight' => $request->birth_weight,
            'farm_id' => $request->farm_id,
            'user_id' => $request->user_id,
            'iot_device_id' => $request->iot_device_id,
            'image' => $request->image,
        ]);

        return redirect()->back()->with(['message' => 'Cattle berhasil ditambahkan', 'status' => 'success']);
    }

    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $cattle = Cattle::findOrFail(new ObjectId($id));

        $request->validate([
            'name' => 'required',
            'breed' => 'required',
            'status' => 'required|in:alive,dead,sold',
            'birth_date' => 'required|date',
            'birth_weight' => 'required|numeric',
            'farm_id' => 'required|exists:farms,id',
            'user_id' => 'required|exists:users,id',
            'iot_device_id' => 'required|exists:iot_devices,id',
        ]);

        $cattle->update([
            'name' => $request->name,
            'breed' => $request->breed,
            'status' => $request->status,
            'birth_date' => $request->birth_date,
            'birth_weight' => $request->birth_weight,
            'farm_id' => $request->farm_id,
            'user_id' => $request->user_id,
            'iot_device_id' => $request->iot_device_id,
            'image' => $request->image,
        ]);

        return redirect()->route('cattle.index')->with(['message' => 'Cattle berhasil di update', 'status' => 'success']);
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt(new ObjectId($id));
        Cattle::findOrFail($id)->delete();
        return redirect()->route('cattle.index')->with(['message' => 'Cattle berhasil di delete', 'status' => 'success']);
    }
}
