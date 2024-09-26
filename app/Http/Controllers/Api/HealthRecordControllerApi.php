<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use Illuminate\Http\Request;

class HealthRecordControllerApi extends Controller
{
    // Mengambil semua rekaman kesehatan
    public function index()
    {
        $healthRecords = HealthRecord::all();
        return response()->json([
            'status' => 'sukses',
            'data' => $healthRecords,
        ]);
    }

    // Menyimpan rekaman kesehatan baru
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'cattle_id' => 'required|integer',
                'checkup_time' => 'required|date',
                'temperature' => 'required|numeric',
                'heart_rate' => 'required|integer',
                'status' => 'required|in:sick,healthy',
                'weight' => 'nullable|numeric',
                'veterinarian' => 'nullable|string|max:255',
            ]);

            $healthRecord = HealthRecord::create($validatedData);
            return response()->json([
                'status' => 'sukses',
                'data' => $healthRecord,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Terjadi kesalahan saat menyimpan rekaman kesehatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mengambil rekaman kesehatan spesifik
    public function show($id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        return response()->json([
            'status' => 'sukses',
            'data' => $healthRecord,
        ]);
    }

    // Memperbarui rekaman kesehatan
    public function update(Request $request, $id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        $validatedData = $request->validate([
            'checkup_time' => 'nullable|date',
            'temperature' => 'nullable|numeric',
            'heart_rate' => 'nullable|integer',
            'status' => 'nullable|in:sick,healthy',
            'weight' => 'nullable|numeric',
            'veterinarian' => 'nullable|string|max:255',
        ]);

        $healthRecord->update($validatedData);
        return response()->json([
            'status' => 'sukses',
            'data' => $healthRecord,
        ]);
    }

    // Menghapus rekaman kesehatan
    public function destroy($id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        $healthRecord->delete();
        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Rekaman kesehatan berhasil dihapus',
        ]);
    }
}
