<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone_number',
        'role',
        'gender',
        'bio',
        'avatar',
        'farm_id',
        'is_pengangon',
        'upah',  // Pastikan kolom 'upah' ada di tabel users
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relasi antara User dan Farm
    public function farms()
    {
        return $this->belongsTo(Farm::class, 'farm_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'farm_id', 'id');
    }

    // Update status pengangon
    public function updatePengangonStatus($status)
    {
        $this->is_pengangon = $status;
        $this->save();
    }
    protected $appends = ['full_avatar_url'];
    public function getFullAvatarUrlAttribute()
    {
        return $this->avatar ? url('api/getFile/' . $this->avatar) : null;
    }
    public function getDetailPengangon($id)
    {
        try {
            // Ambil data detail pengangon
            $pengangon = User::where('id', $id)
                             ->where('is_pengangon', 1) // Pastikan hanya mengambil data pengangon
                             ->first();

            // Jika tidak ditemukan
            if (!$pengangon) {
                return response()->json([
                    'status' => 'gagal',
                    'message' => 'Data pengangon tidak ditemukan',
                    'data' => [],
                ], 404);
            }

            // Ambil riwayat pelanggan yang terkait dengan pengangon
            $riwayatPelanggan = DB::table('users as u') // Tabel users (pelanggan)
                                  ->join('farms as f', 'f.user_id', '=', 'u.id')
                                  ->join('cattle as c', 'c.farm_id', '=', 'f.id')
                                  ->where('f.user_id', $id) // Menyesuaikan pengangon dengan user_id farm
                                  ->select('u.name as customer_name', 'c.name as cow')
                                  ->get();

            // Format data
            $result = [
                'pengangon' => [
                    'id' => $pengangon->id,
                    'name' => $pengangon->name,
                    'address' => $pengangon->address ?? 'Alamat tidak tersedia',
                    'upah' => "Rp " . number_format($pengangon->upah, 0, ',', '.'),
                    'avatar' => $pengangon->avatar ?? null,
                    'bio' => $pengangon->bio ?? 'Bio tidak tersedia',
                    'rate' => $pengangon->rate ?? 0,
                    'durasi_mengangon' => $pengangon->durasi_mengangon ?? 'Tidak tersedia',
                ],
                'riwayat_pelanggan' => $riwayatPelanggan
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
}
