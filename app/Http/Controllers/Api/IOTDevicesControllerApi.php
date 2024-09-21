<?php

namespace App\Http\Controllers\Api;

use App\Models\IOTDevices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;

class IOTDevicesControllerApi extends Controller
{
    public function index()
    {
        $devices = IOTDevices::latest()->get();

        return response()->json([
            'message' => 'Data perangkat IoT',
            'status' => 'success',
            'data' => $devices
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_type' => 'required|string',
            'serial_number' => 'required|string|unique:iot_devices',
            'installation_date' => 'required|date',
            'status' => 'required|string',
            'location' => 'nullable|string',
        ]);

        $device = IOTDevices::create($request->all());

        return response()->json([
            'message' => 'Perangkat IoT berhasil ditambahkan',
            'status' => 'success',
            'data' => $device
        ], 201);
    }

    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $device = IOTDevices::find($id);

        if (!$device) {
            return response()->json(['message' => 'Perangkat IoT tidak ditemukan', 'status' => 'error'], 404);
        }

        return response()->json($device);
    }

    public function update(Request $request, $id)
    {
        $device = IOTDevices::find($id);

        if (!$device) {
            return response()->json(['message' => 'Perangkat IoT tidak ditemukan', 'status' => 'error'], 404);
        }

        $request->validate([
            'device_type' => 'required|string',
            'serial_number' => 'required|string|unique:iot_devices,serial_number,' . $device->id,
            'installation_date' => 'required|date',
            'status' => 'required|string',
            'location' => 'nullable|string',
        ]);

        $device->update($request->all());

        return response()->json([
            'message' => 'Perangkat IoT berhasil diupdate',
            'status' => 'success',
            'data' => $device
        ], 200);
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $device = IOTDevices::find($id);

        if (!$device) {
            return response()->json(['message' => 'Perangkat IoT tidak ditemukan', 'status' => 'error'], 404);
        }

        $device->delete();

        return response()->json([
            'message' => 'Perangkat IoT berhasil dihapus',
            'status' => 'success'
        ], 200);
    }
}
