<?php

namespace App\Http\Controllers;

use App\Models\IOTDevices;
use Illuminate\Http\Request;

use App\Models\{Cattle, User};
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class IOTDevicesController extends Controller
{
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
                    <button type="button" title="EDIT" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateDevice"
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
                ->editColumn('status', function ($row) {
                    if ($row->status == 'active') {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('qr_image', function ($row) {
                    if ($row->qr_image != null) {
                        $qr_image = '<img src="' . asset('storage/' . $row->qr_image) . '" style="width: 100px;  height: 100px; object-fit: cover;">';
                    } else {
                        $qr_image = '<img src="' . url('assets/img/noimage.jpg') . '" style="width: 100px;  height: 100px; object-fit: cover;">';
                    }
                    return $qr_image;
                })
                ->rawColumns(['action', 'status', 'qr_image'])
                ->make(true);
        }
        return view('admin.devices', [
            'data' => IoTDevices::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'installation_date' => 'required|date',
            'status' => 'required|string',
        ]);
        $qrCodeContent = $request->serial_number;
        $qrCodeFileName = 'qrcodes/' . $request->serial_number . '.png';
        $qrCodeImage = QrCode::format('png')->size(200)->generate($qrCodeContent);
        Storage::disk('public')->put($qrCodeFileName, $qrCodeImage);
        IoTDevices::create([
            'serial_number' => $request->serial_number,
            'status' => $request->status,
            'installation_date' => $request->installation_date,
            'qr_image' => $qrCodeFileName,
        ]);
        return redirect()->back()->with(['message' => 'IoT Device berhasil ditambahkan', 'status' => 'success']);
    }

    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $iotDevice = IoTDevices::findOrFail($id);
        $request->validate([
            'serial_number' => 'required|string',
            'installation_date' => 'required|date',
            'status' => 'required|string',
        ]);
        $iotDevice->update([
            'serial_number' => $request->serial_number,
            'status' => $request->status,
            'installation_date' => $request->installation_date,
        ]);
        return redirect()->back()->with(['message' => 'IoT Device berhasil diupdate', 'status' => 'success']);
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        IoTDevices::where('id', $id)->delete();
        return redirect()->route('iotdevice.index')->with(['message' => 'IoT Device berhasil di delete', 'status' => 'success']);
    }
}
