<?php

namespace App\Http\Controllers;

use App\Models\IOTDevices;
use Illuminate\Http\Request;

use App\Models\{Cattle, User};
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use MongoDB\BSON\ObjectId;

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
                    data-device_type="' . $row->device_type . '"
                    data-serial_number="' . $row->serial_number . '"
                    data-status="' . $row->status . '"
                    data-installation_date="' . $row->installation_date . '"
                    data-location="' . $row->location . '"
                    data-url="' . route('iotdevice.update', ['id' => $id]) . '"
                    >
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
                ->rawColumns(['action'])
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
            'device_type' => 'required|string',
            'serial_number' => 'required|string',
            'installation_date' => 'required|date',
            'status' => 'required|string',
        ]);

        IoTDevices::create([
            'device_type' => $request->device_type,
            'serial_number' => $request->serial_number,
            'status' => $request->status,
            'installation_date' => $request->installation_date,
            'location' => $request->location,
        ]);

        return redirect()->back()->with(['message' => 'IoT Device berhasil ditambahkan', 'status' => 'success']);
    }

    // Update IoT device details
    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $iotDevice = IoTDevices::findOrFail(new ObjectId($id));

        $request->validate([
            'device_type' => 'required|string',
            'serial_number' => 'required|string|unique:iotdevices,serial_number,' . $id,
            'installation_date' => 'required|date',
            'status' => 'required|string',
        ]);

        $iotDevice->update([
            'device_type' => $request->device_type,
            'serial_number' => $request->serial_number,
            'status' => $request->status,
            'installation_date' => $request->installation_date,
            'location' => $request->location,
        ]);

        return redirect()->route('iotdevice.index')->with(['message' => 'IoT Device berhasil di update', 'status' => 'success']);
    }

    // Delete IoT device
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        IoTDevices::where('_id', new ObjectId($id))->delete();
        return redirect()->route('iotdevice.index')->with(['message' => 'IoT Device berhasil di delete', 'status' => 'success']);
    }
}