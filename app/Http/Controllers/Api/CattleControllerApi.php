<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Cattle, Farm, Breed, IOTDevices};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CattleControllerApi extends Controller
{
    public function index()
    {
        $user = Auth::id();

        $cattles = Cattle::where('user_id', $user)
            ->with(['iotDevice', 'breed', 'farm', 'healthRecords'])
            ->get()
            ->makeHidden(['created_at', 'updated_at', 'farm_id', 'user_id']);

        return response()->json([
            'message' => 'Data Sapi dan Perangkat IoT',
            'status' => 'success',
            'data' => $cattles
        ]);
    }


    // Menyimpan data sapi baru
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([

                'breed_id' => 'required|exists:breeds,id',
                'status' => 'required|in:sehat,sakit,mati,dijual',
                'gender' => 'required|in:jantan,betina',
                'type' => 'required|in:pedaging,perah,peranakan,lainnya',
                'birth_date' => 'required|date',
                'birth_weight' => 'required|numeric',
                'birth_height' => 'nullable|numeric',
                'iot_device_id' => 'nullable|exists:iot_devices,id',
                'last_vaccination' => 'nullable|date',
            ]);

            $user = Auth::id();
            $farm = Farm::where('user_id', $user)->first();
            $farm_id = $farm ? $farm->id : null;

            $iot_device_id = $request->iot_device_id;



            if (Cattle::where('iot_device_id', $iot_device_id)->exists()) {
                return response()->json([
                    'message' => 'Perangkat IoT sudah digunakan',
                    'status' => 'error'
                ], 400);
            }

            $cattleCount = Cattle::where('user_id', $user)->count();

            $cattle = Cattle::create([
                'name' => 'Sapi ' . ($cattleCount + 1),
                'breed_id' => $validatedData['breed_id'],
                'status' => $validatedData['status'],
                'gender' => $validatedData['gender'],
                'type' => $validatedData['type'],
                'birth_date' => $validatedData['birth_date'],
                'birth_weight' => $validatedData['birth_weight'],
                'birth_height' => $validatedData['birth_height'],
                'farm_id' => $farm_id,
                'user_id' => $user,
                'iot_device_id' => $iot_device_id,
                'last_vaccination' => $validatedData['last_vaccination'],
            ]);




            return response()->json([
                'message' => 'Sapi berhasil ditambahkan',
                'status' => 'success',
                'data' => $cattle
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }


    // Fungsi untuk mendapatkan iot_device_id yang tersedia
    private function getAvailableIotDeviceId()
    {
        // Mengambil perangkat IoT yang belum terpakai
        $availableDevice = IOTDevices::whereNull('cattle')->first(); // Perbaiki nama kelas di sini
        return $availableDevice ? $availableDevice->id : null; // Mengembalikan id atau null jika tidak ada
    }


    // Mengambil semua breed
    public function getBreeds()
    {
        $breeds = Breed::all();

        return response()->json([
            'message' => 'Opsi Breed',
            'status' => 'success',
            'data' => $breeds,
        ], 200);
    }

    // Menampilkan detail sapi berdasarkan ID
    public function show($id)
    {
        $cattle = Cattle::with(['iotDevice', 'breed', 'farm', 'healthRecords'])->findOrFail($id);
        $cattle->makeHidden(['created_at', 'updated_at', 'farm_id', 'user_id']); // Menyembunyikan atribut

        return response()->json([
            'message' => 'Data Sapi ditemukan',
            'status' => 'success',
            'data' => [
                'id' => $cattle->id,
                'name' => $cattle->name,
                'breed' => [
                    'id' => $cattle->breed->id,
                    'name' => $cattle->breed->name,
                ],
                'status' => $cattle->status,
                'gender' => $cattle->gender,
                'type' => $cattle->type,
                'birth_date' => $cattle->birth_date,
                'birth_weight' => $cattle->birth_weight,
                'birth_height' => $cattle->birth_height,
                'last_vaccination' => $cattle->last_vaccination,
                'farm' => [
                    'id' => optional($cattle->farm)->id,
                    'name' => optional($cattle->farm)->name,
                ],
                'iotDevice' => [
                    'id' => optional($cattle->iotDevice)->id,
                    'serial_number' => optional($cattle->iotDevice)->serial_number,
                    'installation_date' => optional($cattle->iotDevice)->installation_date,
                ],
                'healthRecords' => $cattle->healthRecords->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date,
                        'status' => $record->status,
                        'temperature' => $record->temperature,
                    ];
                })
            ]
        ]);
    }

    // Mengupdate data sapi berdasarkan ID
    public function update(Request $request, $id)
    {
        $cattle = Cattle::find($id);

        if (!$cattle) {
            return response()->json([
                'message' => 'Cattle tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        try {
            // Validasi data input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'breed_id' => 'required|exists:breeds,id',
                'status' => 'required|in:sehat,sakit,mati,dijual',
                'gender' => 'required|in:jantan,betina',
                'type' => 'required|in:pedaging,perah,peranakan,lainnya',
                'birth_date' => 'required|date',
                'birth_weight' => 'required|numeric',
                'birth_height' => 'nullable|numeric',
                'iot_device_id' => 'nullable|exists:iot_devices,id',
                'last_vaccination' => 'nullable|date',
            ]);

            // Update data sapi
            $cattle->update([
                'name' => $validatedData['name'],
                'breed_id' => $validatedData['breed_id'],
                'status' => $validatedData['status'],
                'gender' => $validatedData['gender'],
                'type' => $validatedData['type'],
                'birth_date' => $validatedData['birth_date'],
                'birth_weight' => $validatedData['birth_weight'],
                'birth_height' => $validatedData['birth_height'],
                'iot_device_id' => $validatedData['iot_device_id'],
                'last_vaccination' => $validatedData['last_vaccination'],
            ]);

            return response()->json([
                'message' => 'Sapi berhasil diupdate',
                'status' => 'success',
                'data' => $cattle
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }

    // Menghapus data sapi berdasarkan ID
    public function destroy($id)
    {
        $cattle = Cattle::find($id);

        if (!$cattle) {
            return response()->json([
                'message' => 'Cattle tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $cattle->delete();

        return response()->json([
            'message' => 'Cattle berhasil dihapus',
            'status' => 'success'
        ], 200);
    }
}
