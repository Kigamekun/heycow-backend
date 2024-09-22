<?php

namespace App\Http\Controllers;

use App\Models\{Cattle, Farm, User, IOTDevices};
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
                    $file_image = strpos($row->image, 'https://') == 0 ? $row->image : url('storage/' . $row->avatar);
                    $btn = '<div class="d-flex" style="gap:5px;">';
                    $btn .= '
                    <button type="button" title="EDIT" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateCattle"
                    data-name="' . $row->name . '"
                    data-breed="' . $row->breed . '"
                    data-status="' . $row->status . '"
                    data-gender="' . $row->gender . '"
                    data-birth_date="' . $row->birth_date . '"
                    data-birth_weight="' . $row->birth_weight . '"
                    data-birth_height="' . $row->birth_height . '"
                    data-farm_id="' . $row->farm_id . '"
                    data-user_id="' . $row->user_id . '"
                    data-iot_device_id="' . $row->iot_device_id . '"
                    data-image="' . $row->image . '"
                    data-url="' . route('cattle.update', ['id' => $id]) . '">
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
                ->addColumn('farm_name', function ($row) {
                    return $row->farm ? $row->farm->name : 'N/A';
                })
                ->addColumn('iot', function ($row) {
                    return $row->iotDevice ? $row->iotDevice->serial_number : 'N/A';
                })
                ->addColumn('image', function ($row) {
                    if ($row->image != null) {
                        $file_path = strpos($row->image, 'https://') === 0 ? $row->image : url('storage/' . $row->image);
                        return '<img src="' . $file_path . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
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
            'iot_devices' => IOTDevices::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Get the current user
        $user = Auth::user();

        // Conditional validation: If the user is an admin, 'user_id' is required
        $request->validate([
            'name' => 'required',
            'breed' => 'required',
            'status' => 'required|in:alive,dead,sold',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'birth_weight' => 'required|numeric',
            'birth_height' => 'required|numeric',
            'farm_id' => 'required|exists:farms,id',
            'user_id' => $user->is_admin ? 'required|exists:users,id' : '',
            'iot_device_id' => 'required|exists:iot_devices,id',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = null;
        if ($request->hasFile('image')){
            $image = $request->file('image')->store('image','public');
        } else {
            $name = $request->input('name');
            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=128&background=random';
        }


        // If the user is not an admin, set 'user_id' to the currently logged-in user's ID
        $userId = $user->is_admin ? $request->user_id : $user->id;

        Cattle::create([
            'name' => $request->name,
            'breed' => $request->breed,
            'status' => $request->status,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'birth_weight' => $request->birth_weight,
            'birth_height' => $request->birth_height,
            'farm_id' => $request->farm_id,
            'user_id' => $userId,  // Conditional user_id
            'iot_device_id' => $request->iot_device_id,
            'image' => $image,
        ]);

        return redirect()->back()->with(['message' => 'Cattle successfully added', 'status' => 'success']);
    }

    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $cattle = Cattle::findOrFail($id);

        // Get the current user
        $user = Auth::user();

        // Conditional validation: If the user is an admin, 'user_id' is required
        $request->validate([
            'name' => 'required',
            'breed' => 'required',
            'status' => 'required|in:alive,dead,sold',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'birth_weight' => 'required|numeric',
            'birth_height' => 'required|numeric',
            'farm_id' => 'required|exists:farms,id',
            'user_id' => $user->is_admin ? 'required|exists:users,id' : '',
            'iot_device_id' => 'required|exists:iot_devices,id',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if($request->hasFile('image')){
            $image = $request->file('image')->store('image', 'public');
            $data['image'] = $avatar;
        } else {
            $data['image'] = $user->image;
        }
        // If the user is not an admin, set 'user_id' to the currently logged-in user's ID
        $userId = $user->is_admin ? $request->user_id : $user->id;

        $cattle->update([
            'name' => $request->name,
            'breed' => $request->breed,
            'status' => $request->status,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'birth_weight' => $request->birth_weight,
            'birth_height' => $request->birth_height,
            'farm_id' => $request->farm_id,
            'user_id' => $userId,  // Conditional user_id
            'iot_device_id' => $request->iot_device_id,
            'image' => $image,
        ]);

        return redirect()->route('cattle.index')->with(['message' => 'Cattle successfully updated', 'status' => 'success']);
    }


    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        Cattle::findOrFail($id)->delete();
        return redirect()->route('cattle.index')->with(['message' => 'Cattle berhasil di delete', 'status' => 'success']);
    }
}
