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
            'user_id' => Auth::id()
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


    public function getIOTDevicesByUser()
    {
        $devices = IOTDevices::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'message' => 'Data perangkat IoT',
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
}
