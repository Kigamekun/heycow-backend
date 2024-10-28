<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Cattle, Farm, Breed, IOTDevices, RequestNgangon, Contract};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CattleControllerApi extends Controller
{
    private function getAvailableIotDeviceId()
    {
        // Mengambil perangkat IoT yang belum terpakai
        $availableDevice = IOTDevices::whereNull('cattle')->first(); // Perbaiki nama kelas di sini
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
                // 'iot_device_id' => 'nullable|exists:iot_devices,id',
                // 'last_vaccination' => 'nullable|date',
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
                'breed_id' => 'required|exists:breeds,id',
                'status' => 'required|in:sehat,sakit,mati,dijual',
                'gender' => 'required|in:jantan,betina',
                'type' => 'required|in:pedaging,perah,peranakan,lainnya',
                'birth_date' => 'required|date',
                'birth_weight' => 'required|numeric',
                'birth_height' => 'nullable|numeric',
            ]);

            // Update data sapi
            $cattle->update([
                'breed_id' => $validatedData['breed_id'],
                'status' => $validatedData['status'],
                'gender' => $validatedData['gender'],
                'type' => $validatedData['type'],
                'birth_date' => $validatedData['birth_date'],
                'birth_weight' => $validatedData['birth_weight'],
                'birth_height' => $validatedData['birth_height'],
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

    public function searchIOT(Request $request)
    {
        // Ambil query pencarian dari request
        $query = $request->input('query');

        // Ambil data IoT device dengan paginasi
        $devices = IOTDevices::where('serial_number', 'like', '%' . $query . '%')
            ->limit(10) // Batasi jumlah data yang diambil
            ->get();

        // Kembalikan hasil dalam format JSON
        return response()->json($devices);
    }

    public function monitorHealth($id)
    {
        // Logic for monitoring health can be implemented here
        // For example, return health records
        return response()->json(['message' => 'Monitoring health of cattle ' . $id]);
    }

    public function assignIOTDevices(Request $request, $id)
    {
        $iotDevice = IOTDevices::where('serial_number', $request->iot_device_id)->first();

        if (!$iotDevice) {
            return response()->json([
                'message' => 'Perangkat IoT tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        if (Cattle::where('iot_device_id', $iotDevice->id)->exists()) {
            return response()->json([
                'message' => 'Perangkat IoT sudah digunakan',
                'status' => 'error'
            ], 400);
        }

        Cattle::where('id', $id)->update(['iot_device_id' => $iotDevice->id]);

        return response()->json(['message' => 'IOT devices assigned to cattle ' . $id, 'status' => 'success', 'statusCode' => 200]);
    }

    public function removeIOTDevices(Request $request, $id)
    {
        Cattle::where('id', $id)->update(['iot_device_id' => null]);

        return response()->json(['message' => 'IOT devices removed from cattle ' . $id, 'status' => 'success', 'statusCode' => 200]);
    }

    public function changeStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $cattle = Cattle::findOrFail($id);
        $cattle->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Status changed', 'status' => 'success', 'statusCode' => 200]);
    }

    public function createRequest(Request $request)
    {
        $request->validate([
            'cattle_id' => 'required|exists:cattle,id',
            'duration' => 'required|integer', // durasi dalam hari
        ]);

        $request = RequestNgangon::create([
            'user_id' => auth()->id(),
            'cattle_id' => $request->cattle_id,
            'status' => 'pending',
            'duration' => $request->duration,
        ]);

        return response()->json(['message' => 'Request created', 'status' => 'success', 'data' => $request, 'statusCode' => 201]);
    }

    public function respondToRequest(Request $request, $id)
    {
        $requestNgangon = RequestNgangon::findOrFail($id);

        $requestNgangon->status = $request->input('status') == 'approved' ? 'approved' : 'declined';

        $requestNgangon->save();


        if ($request->status == 'approved') {
            $contract = Contract::create([
                'request_id' => $requestNgangon->id,
                'cattle_id' => $requestNgangon->cattle_id,
                'farm_id' => auth()->user()->farms['id'],
                'start_date' => now(),
                'end_date' => now()->addDays($requestNgangon->duration),
                'rate' => 10,
                'initial_weight' => 50,
                'initial_height' => 150,
                'status' => 'active',
            ]);
        } else {
            $contract = null;
        }

        return response()->json([
            'message' => 'Request responded',
            'status' => 'success',
            'statusCode' => 200,
            'data' =>
                [
                    'request' => $requestNgangon,
                    'contract' => $contract
                ]
        ]);
    }

    public function completeContract($id)
    {
        $contract = Contract::findOrFail($id);

        $contract->update([
            'status' => 'completed',
            'final_weight' => 100, // Bisa di-update dengan data terbaru
            'final_height' => 170,
            'end_date' => now(),
        ]);

        // Hitung biaya jasa sesuai rate
        $total_cost = $contract->rate * $contract->duration;
        return response()->json(['message' => 'Contract completed', 'status' => 'success', 'statusCode' => 200, 'data' => $contract, 'total_cost' => $total_cost]);
    }
}
