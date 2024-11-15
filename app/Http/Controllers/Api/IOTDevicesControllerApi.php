<?php

namespace App\Http\Controllers\Api;

use App\Models\Cattle;
use App\Models\IOTDevices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use Illuminate\Support\Facades\Auth;

class IOTDevicesControllerApi extends Controller
{
    public function index()
    {
        // Eager load 'user' relationship to get user data for each device
        $devices = IOTDevices::with('user')->latest()->get();

        // Map the devices and add 'user_name' if 'user' relation exists
        $devices = $devices->map(function ($device) {
            return [
                'id' => $device->id,
                'serial_number' => $device->serial_number,
                'status' => $device->status,
                'installation_date' => $device->installation_date,
                'qr_image' => $device->qr_image,
                'ssid' => $device->ssid,
                'password' => $device->password,
                'user_id' => $device->user_id,
                'user_name' => $device->user ? $device->user->name : null,
            ];
        });

        return response()->json([
            'message' => 'Data perangkat IoT',
            'status' => 'success',
            'data' => $devices
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|unique:iot_devices,serial_number',
            'installation_date' => 'required|date',
            'status' => 'required|string',
        ]);
        $qrCodeContent = $request->serial_number;
        $qrCodeFileName = 'qrcodes/' . $request->serial_number . '.png';
        $qrCodeImage = QrCode::format('png')->size(200)->generate($qrCodeContent);
        Storage::disk('public')->put($qrCodeFileName, $qrCodeImage);
        $device = IoTDevices::create([
            'serial_number' => $request->serial_number,
            'status' => $request->status,
            'installation_date' => $request->installation_date,
            'qr_image' => $qrCodeFileName,
        ]);


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
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($request->hasFile('qr_image')) {
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

    public function assignIOTDevices(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:iot_devices,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $device = IOTDevices::find($request->device_id);

        if (!$device) {
            return response()->json(['message' => 'Perangkat IoT tidak ditemukan', 'status' => 'error'], 404);
        }

        $device->user_id = $request->user_id;
        $device->save();

        return response()->json([
            'message' => 'Perangkat IoT berhasil di-assign ke pengguna',
            'status' => 'success',
            'data' => $device
        ], 200);
    }

    public function getIOTDevicesByUser()
    {
        $devices = IOTDevices::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'message' => 'Data perangkat IoT milik pengguna',
            'status' => 'success',
            'data' => $devices
        ]);
    }

    public function changeStatus(Request $request, $id)
    {
        $device = IOTDevices::find($id);

        if (!$device) {
            return response()->json(['message' => 'Perangkat IoT tidak ditemukan', 'status' => 'error'], 404);
        }

        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $device->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status perangkat IoT berhasil diubah',
            'status' => 'success',
            'data' => $device
        ], 200);
    }

    public function removeIOTDevices($id) {
        $device = IOTDevices::find($id);

        if (!$device) {
            return response()->json(['message' => 'Perangkat IoT tidak ditemukan', 'status' => 'error'], 404);
        }

        $device->user_id = null;
        $device->save();

        return response()->json([
            'message' => 'Perangkat IoT berhasil di-unassign dari pengguna',
            'status' => 'success',
            'data' => $device
        ], 200);
    }
}
