<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    // Mengambil pengguna spesifik
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'status' => 'sukses',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Pengguna tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    // Memperbarui pengguna
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|string|min:8',
            ]);

            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $user->update($validatedData);

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Pengguna berhasil diperbarui',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal memperbarui pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Menghapus pengguna
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Pengguna berhasil dihapus',
            ]);
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Login gagal',
                'error' => $e->getMessage(),
            ], 401);
        }
    }
}
