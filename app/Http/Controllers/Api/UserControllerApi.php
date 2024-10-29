<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserControllerApi extends Controller
{
    // Mengambil semua pengguna
    public function index()
    {
        try {
            $users = User::all();
            return response()->json([
                'status' => 'sukses',
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal mengambil data pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Menyimpan pengguna baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        try {
            $validatedData['password'] = Hash::make($validatedData['password']);
            $validatedData['role'] = 'user'; // Atur role menjadi 'user'

            $user = User::create($validatedData);
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Pengguna berhasil dibuat',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal membuat pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Memperbarui data pengguna berdasarkan ID
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        try {
            $user = User::findOrFail($id);

            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $user->update(array_filter($validatedData)); // filter untuk menghindari null

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Pengguna berhasil diperbarui',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal memperbarui pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Menghapus pengguna berdasarkan ID
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Pengguna berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal menghapus pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Login pengguna
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            return response()->json([
                'status' => 'sukses',
                'data' => $user,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Login gagal: ' . $e->getMessage(),
            ], 401);
        }
    }

    // Search, Sort, Limit, Paging
    public function search(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort_by') && in_array($request->sort_by, ['name', 'email'])) {
            $query->orderBy($request->sort_by, $request->sort_direction ?? 'asc');
        }

        $limit = $request->input('limit', 10);
        $users = $query->paginate($limit);

        return response()->json([
            'status' => 'sukses',
            'data' => $users,
        ]);
    }

    // Forgot Password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            return response()->json([
                'status' => $status === Password::RESET_LINK_SENT ? 'sukses' : 'gagal',
                'pesan' => $status === Password::RESET_LINK_SENT ? 'Link reset password berhasil dikirim' : 'Gagal mengirim link reset password',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal mengirim link reset password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }   

    // Menampilkan pengguna berdasarkan ID
    public function show($id)
    {
        try {
            $user = User::findOrFail($id); // Mencari pengguna berdasarkan ID
            return response()->json([
                'status' => 'sukses',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal mengambil data pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Change Password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Password saat ini salah',
                ], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Password berhasil diubah',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal mengubah password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Request IOT Device
    public function requestIOT(Request $request)
    {
        $request->validate([
            'iot' => 'required|string|exists:iot_devices,id',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            // Logika request IOT device di sini
            // Misalnya, membuat request untuk perangkat tertentu

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Permintaan IOT berhasil diajukan',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal melakukan permintaan IOT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Assign Farm to User
    public function assignFarm(Request $request, $userId)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
        ]);

        try {
            $user = User::findOrFail($userId);
            $user->farm_id = $request->farm_id; // Asumsi ada kolom `farm_id` di tabel `users`
            $user->save();

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Farm berhasil ditugaskan ke pengguna',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal menugaskan farm',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
