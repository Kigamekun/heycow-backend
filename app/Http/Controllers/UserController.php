<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Crypt;


class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('avatar', function ($row) {
                    $file_path = strpos($row->avatar, 'https://') === 0 ? $row->avatar : url('storage/' . $row->avatar);
                    return '<img src="' . $file_path . '" style="width: 50px; border-radius:50%; height: 50px; object-fit: cover;">';
                })
                ->addColumn('action', function ($row) {
                    $id = Crypt::encrypt($row->id);
                    $file_path = strpos($row->avatar, 'https://') === 0 ? $row->avatar : url('storage/' . $row->avatar);
                    return '
                        <div class="d-flex" style="gap:5px;">
                            <button type="button" title="EDIT" class="btn btn-sm btn-warning btn-edit" data-toggle="modal" data-target="#updateData"
                                data-url="' . route('user.update', ['id' => $id]) . '"
                                data-id="' . $id . '" data-name="' . $row->name . '"
                                data-email="' . $row->email . '"
                                data-gender="' . $row->gender . '"
                                data-bio="' . $row->bio . '"
                                data-avatar="' . $file_path . '"
                                data-address="' . $row->address . '"
                                data-phone_number="' . $row->phone_number . '"
                                data-role="' . $row->role . '">
                                Edit
                            </button>
                            <form id="deleteForm" action="' . route('user.delete', ['id' => $id]) . '" method="POST">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="button" title="DELETE" class="btn btn-sm btn-danger btn-delete" onclick="confirmDelete(event)">
                                    Delete
                                </button>
                            </form>
                        </div>';
                })
                ->rawColumns(['avatar', 'action'])
                ->make(true);
        }
        $owners = User::where('role', 'cattleman')->get();
        return view('admin.user', ['owners' => $owners]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,cattleman',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|file|image',
        ]);
        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $avatar = null;
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar')->store('avatars', 'public');
        } else {
            $name = $request->input('name');
            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=128&background=random';
        }
        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'role' => $request->input('role'),
            'phone_number' => $request->input('phone_number'),
            'address' => $request->input('address'),
            'avatar' => $avatar, // Save the avatar URL or file path
        ]);
        return redirect()->back()->with(['message' => 'Users berhasil ditambahkan', 'status' => 'success']);
    }

    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,cattleman',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'avatar' => 'nullable|file|image',
        ]);
        $user = User::find($id);
        $data = $request->only(['name', 'email', 'role', 'phone_number', 'address']);
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->input('password'));
        }
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatar;
        } else {
            $data['avatar'] = $user->avatar;
        }
        $user->update($data);
        return redirect()->route('user.index')->with(['message' => 'Users berhasil di update', 'status' => 'success']);
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        User::where('id', $id)->delete();
        return redirect()->route('user.index')->with(['message' => 'Users berhasil di delete', 'status' => 'success']);
    }
}
