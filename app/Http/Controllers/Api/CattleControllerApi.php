<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Cattle, Farm, Breed, IOTDevices,HealthRecord, RequestNgangon, Contract, HistoryRecord};
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
        $user = Auth::id();
        $limit = $_GET['limit'] ?? 10;

        $data = Cattle::with(['iotDevice', 'breed', 'farm', 'healthRecords'])
            ->orderBy('id', 'DESC');

        // Search filter
        if (isset($_GET['search'])) {
            $data = $data->where('name', 'like', '%' . $_GET['search'] . '%');
        }

        // Role-based filtering for cattle data
        if (auth()->user()->role == 'cattleman' && auth()->user()->is_pengangon == 0) {
            // Cattle owned by the logged-in user
            $data = $data->where('user_id', $user);
        } else if (auth()->user()->role == 'cattleman' && auth()->user()->is_pengangon == 1) {

            // Cattle either owned by the caretaker or cared for under an active contract
            $data = $data->where(function ($query) use ($user) {
                $query->where('user_id', $user) // Owned by the logged-in user
                      ->orWhereHas('contracts', function ($query) use ($user) {
                          $query->where('status', 'active')
                                ->whereHas('requestAngon', function ($query) use ($user) {
                                    $query->where('peternak_id', $user);
                                });
                      });
            });

        }

        // Load first health record and adjust farm based on contract status
        $data = $data->get()->map(function ($cattle) {
            // Add the first health record for each cattle, if exists
            $cattle->first_health_record = $cattle->healthRecords()->first();

            // Check if cattle has an active contract
            $activeContract = $cattle->contracts()->where('status', 'active')->first();
            if ($activeContract) {
                $farm = Farm::where('id',$activeContract->farm_id)->first();
                $cattle->farmNow = $farm; // Set farm to caretaker's farm
            } else {
                $farm = Farm::where('id',$cattle->farm_id)->first();
                $cattle->farmNow = $farm; // Set farm to caretaker's farm


            }

            return $cattle;
        });

        // Prepare the response
        if ($data->count() > 0) {
            $custom = collect([
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Data berhasil diambil',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ]);
            return response()->json($custom, 200);
        } else {
            $custom = collect([
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'Data tidak ditemukan',
                'data' => null
            ]);
            return response()->json($custom, 200);
        }
    }


    public function store(Request $request)
    {
        try {
            if (auth()->user()->role == 'cattleman') {
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
            } else {
                $validatedData = $request->validate([
                    'breed_id' => 'required|exists:breeds,id',
                    'status' => 'required|in:sehat,sakit,mati,dijual',
                    'gender' => 'required|in:jantan,betina',
                    'type' => 'required|in:pedaging,perah,peranakan,lainnya',
                    'birth_date' => 'required|date',
                    'birth_weight' => 'required|numeric',
                    'birth_height' => 'nullable|numeric',
                ]);

                $farm_id = $request->farm_id;
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
            }


        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
                'status' => 'error'
            ], 422);
        }
    }


    public function iotDevices($id)
    {

        $iot = IOTDevices::where('serial_number',$id)->first();

        $cattle = Cattle::with(['iotDevice', 'breed', 'farm', 'healthRecords'])->where('iot_device_id',$iot['id'])->first();

        $healthRecords = HealthRecord::where('cattle_id', $cattle->id)->orderBy('created_at', 'DESC')->first();

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
                'healthRecords' => $healthRecords
            ]
        ]);
    }

    public function searchIOT(Request $request)
    {
        // Get the search query from the request
        $query = $request->input('query');

        // Fetch IoT devices not associated with any cattle (iot_device_id not in cattle table)
        $devices = IOTDevices::where('serial_number', 'like', '%' . $query . '%')
            ->where('user_id', Auth::id())
            ->whereNotIn('id', Cattle::whereNotNull('iot_device_id')->pluck('iot_device_id'))
            ->limit(10) // Limit the number of results
            ->get();


        // Return results in JSON format
        return response()->json($devices);
    }

    public function assignIOTDevices(Request $request, $id)
    {

        // Validasi data input
        $request->validate([
            'iot_device_id' => 'required|string|exists:iot_devices,serial_number',
        ]);


        // Cari perangkat IoT berdasarkan serial_number
        $iotDevice = IOTDevices::where('serial_number', $request->iot_device_id)->first();
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

    public function removeIOTDevices(Request $request, $id)
    {
        Cattle::where('id', $id)->update(['iot_device_id' => null]);

        return response()->json(['message' => 'IOT devices removed from cattle ' . $id, 'status' => 'success', 'statusCode' => 200]);
    }

    public function show($id)
    {
        $cattle = Cattle::with(['iotDevice', 'breed', 'farm', 'healthRecords'])->findOrFail($id);
        $cattle->makeHidden(['created_at', 'updated_at', 'farm_id', 'user_id']); // Menyembunyikan atribut

        $healthRecords = HealthRecord::where('cattle_id', $id)->orderBy('created_at', 'DESC')->first();

        $diAngon = !is_null(Contract::where('cattle_id', $id)->where('status', 'active')->first()) ? true : false;


        return response()->json([
            'message' => 'Data Sapi ditemukan',
            'status' => 'success',
            'data' => [
                'diAngon' => $diAngon,
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
                'healthRecords' => $healthRecords
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
                'breed_id' => 'nullable|exists:breeds,id',
                'status' => 'nullable|in:sehat,sakit,mati,dijual',
                'gender' => 'nullable|in:jantan,betina',
                'type' => 'nullable|in:pedaging,perah,peranakan,lainnya',
                'birth_date' => 'nullable|date',
                'birth_weight' => 'nullable|numeric',
                'birth_height' => 'nullable|numeric',
            ]);

            if (isset($request->name) && $request->name != '') {
                $name = $request->name;
            } else {
                $name = $cattle->name;
            }

            // Update data sapi
            $cattle->update([
                'name' => $name,
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
