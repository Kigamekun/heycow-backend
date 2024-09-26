<?php

namespace App\Http\Controllers\Api;

use App\Models\IOTDevices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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
            'serial_number' => 'required|string|unique:iot_devices',
            'installation_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Proses upload gambar
        $qrImagePath = null;
        if ($request->hasFile('qr_image')) {
            $qrImagePath = $request->file('qr_image')->store('qr_images', 'public');
        }

        // Buat perangkat IoT
        $device = IOTDevices::create(array_merge($request->all(), ['qr_image' => $qrImagePath]));

        return response()->json([
            'message' => 'Perangkat IoT berhasil ditambahkan',
            'status' => 'success',
            'data' => $device
        ], 201);
    }

    public function show($id)
    {
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
            'serial_number' => 'required|string|unique:iot_devices,serial_number,' . $device->id,
            'installation_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Proses upload gambar
        if ($request->hasFile('qr_image')) {
            // Hapus gambar lama jika ada
            if ($device->qr_image) {
                Storage::disk('public')->delete($device->qr_image);
            }

            $qrImagePath = $request->file('qr_image')->store('qr_images', 'public');
            $request->merge(['qr_image' => $qrImagePath]);
        }

        $device->update($request->all());

        return response()->json([
            'message' => 'Perangkat IoT berhasil diupdate',
            'status' => 'success',
            'data' => $device
        ], 200);
    }

    public function destroy($id)
    {
        $device = IOTDevices::find($id);

        if (!$device) {
            return response()->json(['message' => 'Perangkat IoT tidak ditemukan', 'status' => 'error'], 404);
        }

        // Hapus gambar jika ada
        if ($device->qr_image) {
            Storage::disk('public')->delete($device->qr_image);
        }

        $device->delete();

        return response()->json([
            'message' => 'Perangkat IoT berhasil dihapus',
            'status' => 'success'
        ], 200);
    }
}
