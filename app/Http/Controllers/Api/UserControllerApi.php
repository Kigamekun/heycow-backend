<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserControllerApi extends Controller
{
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

    public function show()
    {
        try{
        $id = request()->route('id');
        $user = User::findOrFail($id);
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

    public function submitRequestForm(Request $request)
{

    // Validasi data dari form request
    $validatedData = $request->validate([
        'nik' => 'required|string|max:255',
        'ktp' => 'required|file|mimes:jpeg,png,jpg,svg|max:2048',
        'upah' => 'required|numeric',
        'selfie_ktp' => 'required|file|mimes:jpeg,png,jpg,svg|max:2048',
    ]);

    try {
        // Mengambil user yang terautentikasi
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'User tidak ditemukan atau tidak terautentikasi.',
            ], 401);
        }

        // Log the authenticated user
        \Log::info('User Authenticated', ['user' => $user]);

        // Pastikan file valid sebelum disimpan
        $ktpPath = $request->file('ktp')->isValid() ? $request->file('ktp')->store('ktp', 'public') : null;
        $selfieKtpPath = $request->file('selfie_ktp')->isValid() ? $request->file('selfie_ktp')->store('selfie_ktp', 'public') : null;

        // Perbarui data user dengan data yang diterima
        $user->nik = $validatedData['nik'];
        $user->ktp = $ktpPath;
        $user->upah = $validatedData['upah'];
        $user->selfie_ktp = $selfieKtpPath;
        $user->save();

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Pengajuan berhasil dikirim, menunggu persetujuan admin.',
            'user' => $user
        ]);
    } catch (\Exception $e) {
        // Catat error untuk debug jika perlu
        \Log::error('Error saat submit request form: ' . $e->getMessage());
        return response()->json([
            'status' => 'gagal',
            'pesan' => 'Gagal mengirimkan pengajuan',
            'error' => $e->getMessage(),
        ], 500);
    }
}




    public function approveRequest(Request $request, $userId)
    {
        try {
            // Mencari pengguna berdasarkan ID
            $user = User::findOrFail($userId);

            // Mengubah status menjadi 'approved'
            $user->is_pengangon = 1;
            $user->save();

            // Mengembalikan respons jika berhasil
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Pengajuan telah disetujui',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            // Menangani error jika ada masalah
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Gagal menyetujui pengajuan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function rejectRequest($userId)
    {
        try {
            // Temukan user berdasarkan ID
            $user = User::find($userId);

            // Cek apakah user ditemukan
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Update data menjadi null saat request ditolak
            $user->nik = null;
            $user->ktp = null;
            $user->selfie_ktp = null;
            $user->upah = null;

            // Simpan perubahan ke database
            $user->save();

            return response()->json(['message' => 'Request rejected successfully'], 200);
        } catch (\Exception $e) {
            // Tangani error dengan baik dan log pesan error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    // Menyimpan pengguna baru
    public function store(Request $request)
{
    // Validasi hanya data yang relevan untuk pembuatan pengguna baru
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'phone_number' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'avatar' => 'nullable|file|mimes:jpeg,png,jpg,svg|max:2048',
    ]);

    try {
        // Proses upload avatar jika ada
        if (auth()->user()->role == 'cattleman') {
            $avatarPath = $request->file('avatar') ? $request->file('avatar')->store('avatars', 'public') : null;

            // Membuat pengguna baru dengan data yang sudah divalidasi
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'phone_number' => $validatedData['phone_number'],
                'address' => $validatedData['address'],
                'avatar' => $avatarPath,
            ]);
        } else {
            $avatarPath = $request->file('avatar') ? $request->file('avatar')->store('avatars', 'public') : null;

            // Membuat pengguna baru dengan data yang sudah divalidasi
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'phone_number' => $validatedData['phone_number'],
                'address' => $validatedData['address'],
                'avatar' => $avatarPath,
                'role' => $request->input('role'),
            ]);
        }


        // Response sukses jika pengguna berhasil dibuat
        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Pengguna berhasil dibuat',
            'data' => $user,
        ], 201);
    } catch (\Exception $e) {
        // Response gagal jika ada error dalam proses
        return response()->json([
            'status' => 'gagal',
            'pesan' => 'Gagal membuat pengguna',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function getDetailPengangon($id)
    {
        try {
            // Ambil data detail pengangon berdasarkan user_id
      $pengangon = User::where('users.id', $id)->where('users.is_pengangon', 1)
        ->whereNotNull('users.upah')
        ->join('farms', 'farms.user_id', '=', 'users.id')
        ->leftJoin('request_ngangons', 'request_ngangons.peternak_id', '=', 'users.id')
        ->leftJoin(
            DB::raw('(SELECT request_ngangons.peternak_id, COALESCE(AVG(contracts.rate), 0) as avg_rate
                      FROM contracts
                      JOIN request_ngangons ON request_ngangons.id = contracts.request_id
                      GROUP BY request_ngangons.peternak_id) as contract_avg'),
            'contract_avg.peternak_id', '=', 'users.id'
        )
        ->select(
            'users.id',
            'users.name',
            'farms.name as farms',
            'users.address',
            'users.upah',
            'users.avatar',
            DB::raw('COALESCE(contract_avg.avg_rate, 0) as avg_rate')
        )
        ->groupBy('users.id', 'users.name', 'farms.name', 'users.address', 'users.upah', 'users.avatar')->first();
            // Jika tidak ditemukan
            if (!$pengangon) {
                return response()->json([
                    'status' => 'gagal',
                    'message' => 'Data pengangon tidak ditemukan',
                    'data' => [],
                ], 404);
            }

            // Ambil riwayat kontrak yang terkait dengan farm_id dan user_id
            $riwayatPelanggan = DB::table('contracts') // Mengambil data kontrak dari tabel contract
                                  ->join('farms', 'contracts.farm_id', '=', 'farms.id') // Join dengan farms untuk dapatkan nama farm
                                  ->where('farms.user_id', $id) // Filter berdasarkan user_id
                                  ->join('cattle', 'contracts.cattle_id', '=', 'cattle.id') // Gabungkan dengan data sapi
                                  ->join('users', 'farms.user_id', '=', 'users.id') // Join dengan tabel users untuk mengambil nama pelanggan
                                  ->select(
                                      'contracts.start_date',
                                      'contracts.end_date',
                                      'cattle.name as cow_name',
                                      'users.name as customer_name' // Nama pelanggan diambil dari tabel users
                                  )
                                  ->get();

            // Format data untuk riwayat pelanggan
            $riwayatFormatted = $riwayatPelanggan->map(function ($item) {
                $startDate = Carbon::parse($item->start_date);
                $endDate = Carbon::parse($item->end_date);
                $durasi = $startDate->diffInDays($endDate) . ' hari'; // Hitung durasi dalam hari

                return [
                    'durasi' => $durasi,
                    'cow_name' => $item->cow_name,
                    'customer_name' => $item->customer_name,
                ];
            });


        $result = [
            'pengangon' => [
                'id' => $pengangon->id,
                'name' => $pengangon->name,
                'address' => $pengangon->address ?? 'Alamat tidak tersedia',
                'upah' => $pengangon->upah,
                'avatar' => $pengangon->full_avatar_url ?? null,
                'rate' => $pengangon->avg_rate ? (int) $pengangon->avg_rate : 0,
                'farm' => $pengangon->farms ?? 'Farm tidak tersedia',
            ],
            'riwayat_pelanggan' => $riwayatFormatted
        ];


            return response()->json([
                'status' => 'sukses',
                'message' => 'Detail pengangon dan riwayat pelanggan berhasil diambil',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Gagal mengambil data pengangon dan riwayat pelanggan',
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function getUserByPengangon(Request $request)
{
    try {

        $search = $request->input('search', '');

        // Ambil data pengguna dengan filter 'is_pengangon' = 1 dan 'upah' yang valid
        $users = User::where('users.is_pengangon', 1)
        ->whereNotNull('users.upah')
        ->join('farms', 'farms.user_id', '=', 'users.id')
        ->leftJoin('request_ngangons', 'request_ngangons.peternak_id', '=', 'users.id')
        ->leftJoin(
            DB::raw('(SELECT request_ngangons.peternak_id, COALESCE(AVG(contracts.rate), 0) as avg_rate
                      FROM contracts
                      JOIN request_ngangons ON request_ngangons.id = contracts.request_id
                      GROUP BY request_ngangons.peternak_id) as contract_avg'),
            'contract_avg.peternak_id', '=', 'users.id'
        )
        ->select(
            'users.id',
            'users.name',
            'farms.name as farms',
            'users.address',
            'users.upah',
            'users.avatar',
            DB::raw('COALESCE(contract_avg.avg_rate, 0) as avg_rate')
        )
        ->groupBy('users.id', 'users.name', 'farms.name', 'users.address', 'users.upah', 'users.avatar');


        // Jika ada parameter pencarian, filter berdasarkan nama atau farm
        if ($search) {
            $users = $users->where(function ($query) use ($search) {
                $query->where('users.name', 'like', '%' . $search . '%') // Tentukan 'users.name'
                    ->orWhereHas('farms', function ($query) use ($search) {
                        $query->where('farms.name', 'like', '%' . $search . '%'); // Tentukan 'farms.name'
                    });
            });
        }

        // Ambil hasil pencarian
        $users = $users->get();

        // Mengecek apakah data ditemukan
        if ($users->isEmpty()) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Data pengguna pengangon tidak ditemukan',
                'data' => [],
            ], 404);
        }

        // Proses data pengguna
        $result = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'farm' => $user->farms ? $user->farms : 'Farm tidak ditemukan',
                'address' => $user->address ?? 'Alamat tidak tersedia',
                'upah' => $user->upah,
                'avatar' => $user->avatar ?? null,
                'rate' => $user->avg_rate ? (int) $user->avg_rate : 0
            ];
        });


        // Mengembalikan respons dengan status sukses
        return response()->json([
            'status' => 'sukses',
            'message' => 'Data pengguna pengangon berhasil diambil',
            'data' => $result,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'gagal',
            'message' => 'Gagal mengambil data pengguna',
            'data' => [],
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function update(Request $request, $id)
{
    try {
        // Validasi data input
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'phone_number' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
        ]);

        // Hash password jika ada
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // Simpan avatar jika ada
        if ($request->hasFile('avatar')) {
            $validatedData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Cari user berdasarkan ID
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update user dengan data yang telah divalidasi
        $user->update($validatedData);

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

    public function search(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->get();

        return response()->json([
            'status' => 'sukses',
            'data' => $users
        ]);
    }
}
