<?php

namespace App\Http\Controllers;

use App\Models\IOTDevices;
use Illuminate\Http\Request;

use App\Models\{Cattle, User};
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;

class IOTDevicesController extends Controller
{
    // Show IoT Devices in index view
    public function index(Request $request)
    {
        $data = IoTDevices::latest()->get();
        if ($request->ajax()) {
            $data = IoTDevices::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $id = Crypt::encrypt($row->id);
                    $btn = '<div class="d-flex" style="gap:5px;">';
                    $btn .= '
                    <button type="button" title="EDIT" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateData"
                    data-serial_number="' . $row->serial_number . '"
                    data-status="' . $row->status . '"
                    data-installation_date="' . $row->installation_date . '"
                    data-url="' . route('iotdevice.update', ['id' => $id]) . '">
                        Edit
                    </button>';
                    $btn .= '
                    <form id="deleteForm" action="' . route('iotdevice.delete', ['id' => $id]) . '" method="POST">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                                <button type="button" title="DELETE" class="btn btn-sm btn-danger btn-delete" onclick="confirmDelete(event)">
                                    Delete
                                </button>
                            </form>
                    </div>';
                    return $btn;
                })
                ->addColumn('qr_image', function ($row) {
                    if ($row->qr_image != null) {
                        $qr_image = '<img src="' . asset('storage/' . $row->qr_image) . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                    } else {
                        $qr_image = '<img src="' . url('assets/img/noimage.jpg') . '" style="width: 100px; border-radius:20px; height: 100px; object-fit: cover;">';
                    }
                    return $qr_image;
                })
                ->rawColumns(['action','qr_image'])
                ->make(true);
        }
        return view('admin.devices', [
            'data' => IoTDevices::all()
        ]);
    }

    // Store new IoT device
    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'installation_date' => 'required|date',
            'status' => 'required|string',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $qr_image = null;
        if ($request->hasFile('qr_image')) {
            $qrImage = $request->file('qr_image');
            $qrImagePath = $qrImage->store('qrcodes', 'public');
        }

        IoTDevices::create([
            'serial_number' => $request->serial_number,
            'status' => $request->status,
            'installation_date' => $request->installation_date,
            'qr_image' => $qrImagePath,
        ]);

        return redirect()->back()->with(['message' => 'IoT Device berhasil ditambahkan', 'status' => 'success']);
    }

    // Update IoT device details
    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $iotDevice = IoTDevices::findOrFail($id);

        $request->validate([

            'serial_number' => 'required|string|unique:iotdevices,serial_number,' . $id,
            'installation_date' => 'required|date',
            'status' => 'required|string',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $qrImagePath = $iotDevice->qr_image;  // Keep existing image path
    if ($request->hasFile('qr_image')) {
        // Delete the old image if it exists
        if ($qrImagePath) {
            Storage::disk('public')->delete($qrImagePath);
        }

        // Store the new QR image
        $qrImage = $request->file('qr_image');
        $qrImagePath = $qrImage->store('qrcodes', 'public');
    }

        $iotDevice->update([
            'serial_number' => $request->serial_number,
            'status' => $request->status,
            'installation_date' => $request->installation_date,
            'qr_image' => $request->$qrImagePath,
        ]);

        return redirect()->route('iotdevice.index')->with(['message' => 'IoT Device berhasil di update', 'status' => 'success']);
    }

    // Delete IoT device
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        IoTDevices::where('id', $id)->delete();
        return redirect()->route('iotdevice.index')->with(['message' => 'IoT Device berhasil di delete', 'status' => 'success']);
    }
}
