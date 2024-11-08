<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Cattle, Farm, Breed, IOTDevices, RequestNgangon, Contract, HistoryRecord};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CattleControllerApi extends Controller
{
    private function getAvailableIotDeviceId()
    {
        // Mengambil perangkat IoT yang belum terpakai
        $availableDevice = IOTDevices::whereNull('cattle')->first();
        return $availableDevice ? $availableDevice->id : null; // Mengembalikan id atau null jika tidak ada
    }

    public function getBreeds()
    {
        $breeds = Breed::all();

        return response()->json([
            'message' => 'Opsi Breed',
            'status' => 'success',
            'data' => $breeds,
        ], 200);
    }

    public function index()
    {
        $limit = $_GET['limit'] ?? 10;
        $data = Cattle::with(['iotDevice', 'breed', 'farm'])->orderBy('id', 'DESC');
        if (isset($_GET['search'])) {
            $data = $data->where('name', 'like', '%' . $_GET['search'] . '%');
        }
        if (auth()->user()->role == 'user' || auth()->user()->role == 'cattleman') {
            $data = $data->where('user_id', auth()->id());
        }
        if ($data->count() > 0) {
            $data = $data->paginate($limit);
            $custom = collect(['status' => 'success', 'statusCode' => 200, 'message' => 'Data berhasil diambil', 'data' => $data, 'timestamp' => now()->toIso8601String()]);
            return response()->json($custom, 200);
        } else {
            $custom = collect(['status' => 'error', 'statusCode' => 404, 'message' => 'Data tidak ditemukan', 'data' => null]);
            return response()->json($custom, 200);
        }
    }

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
            ]);

            $user = Auth::id();
            $farm = Farm::where('user_id', $user)->first();
            $farm_id = $farm ? $farm->id : null;
            $iot_device_id = $request->iot_device_id;

            if ($iot_device_id != null && Cattle::where('iot_device_id', $iot_device_id)->exists()) {
                return response()->json([
                    'message' => 'Perangkat IoT sudah digunakan',
                    'status' => 'error'
                ], 400);
            }

            $name = $request->name ?? 'Sapi ' . (Cattle::where('user_id', $user)->count() + 1);
            $cattle = Cattle::create([
                'name' => $name,
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
            ]);

            HistoryRecord::create([
                'cattle_id' => $cattle->id,
                'record_type' => 'create',
                'new_value' => $cattle->toJson(),
                'recorded_at' => now(),
                'created_by' => $user,
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
        }
    }

    public function assignIOTDevices(Request $request, $id)
    {
        // Validasi data input
        $request->validate([
            'iot_devices_id' => 'required|string|exists:iot_devices,serial_number',
        ]);

        // Cari perangkat IoT berdasarkan serial_number
        $iotDevice = IOTDevices::where('serial_number', $request->iot_devices_id)->first();
        if (!$iotDevice) {
            return response()->json([
                'message' => 'Perangkat IoT tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        // Cari sapi berdasarkan ID
        $cattle = Cattle::findOrFail($id);
        
        // Asumsikan kamu memiliki relasi antara sapi dan perangkat IoT
        $cattle->iot_device_id = $iotDevice->id;  // Atau gunakan method associate jika memakai relasi
        $cattle->save();

        return response()->json([
            'message' => 'Perangkat IoT berhasil diassign ke sapi',
            'status' => 'success',
            'data' => $cattle
        ], 200);
    }

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
                'iot_device_id' => optional($cattle->iotDevice)->id,
                'birth_date' => $cattle->birth_date,
                'birth_weight' => $cattle->birth_weight,
                'birth_height' => $cattle->birth_height,
                'last_vaccination' => $cattle->last_vaccination,
                'farm' => [
                    'id' => optional($cattle->farm)->id,
                    'name' => optional($cattle->farm)->name,
                ],
                'iot_device' => [
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

    public function update(Request $request, $id)
{
    try {
        $validatedData = $request->validate([
            'status' => 'required|in:sehat,sakit,mati,dijual',
            'gender' => 'required|in:jantan,betina',
            'type' => 'required|in:pedaging,perah,peranakan,lainnya',
            'birth_date' => 'required|date',
            'birth_weight' => 'required|numeric',
            'birth_height' => 'nullable|numeric',
        ]);

        $user = Auth::id();
        $cattle = Cattle::findOrFail($id);

        // Menyimpan old_value dan new_value hanya jika ada perubahan
        $oldData = $cattle->getAttributes();
        $cattle->update($validatedData);
        $newData = $cattle->getAttributes();

        // Bandingkan data lama dan baru
        $changes = $this->getChanges($oldData, $newData);

        if (!empty($changes)) {
            // Log perubahan ke history
            HistoryRecord::create([
                'cattle_id' => $cattle->id,
                'record_type' => 'update',
                'old_value' => json_encode($changes['old_value']),
                'new_value' => json_encode($changes['new_value']),
                'recorded_at' => now(),
                'created_by' => $user,
            ]);
        }

        return response()->json([
            'message' => 'Sapi berhasil diperbarui',
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

private function getChanges($oldData, $newData)
{
    $changes = [
        'old_value' => [],
        'new_value' => []
    ];

    foreach ($oldData as $key => $oldValue) {
        if (array_key_exists($key, $newData) && $oldValue != $newData[$key]) {
            $changes['old_value'][$key] = $oldValue;
            $changes['new_value'][$key] = $newData[$key];
        }
    }

    return $changes;
}


    public function destroy($id)
    {
        $cattle = Cattle::find($id);

        if (!$cattle) {
            return response()->json([
                'message' => 'Cattle tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $oldValue = $cattle->toJson();
        $cattle->delete();

        HistoryRecord::create([
            'cattle_id' => $id,
            'record_type' => 'delete',
            'old_value' => $oldValue,
            'recorded_at' => now(),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Sapi berhasil dihapus',
            'status' => 'success'
        ], 200);
    }
}
