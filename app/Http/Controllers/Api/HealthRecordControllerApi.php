<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\Cattle;
use Illuminate\Http\Request;

class HealthRecordControllerApi extends Controller
{
    // Mengambil semua rekaman kesehatan, dipisahkan berdasarkan bulan dan tahun
    public function index()
    {
        $healthRecords = HealthRecord::all();

        // Mengelompokkan berdasarkan bulan dan tahun
        $groupedRecords = $healthRecords->groupBy(function ($item) {
            return $item->checkup_time->format('F Y'); // Menggunakan bulan dan tahun untuk grup
        });

        // Format data untuk ditampilkan
        $formattedRecords = [];

        foreach ($groupedRecords as $period => $records) {
            $monthYear = explode(' ', $period);
            $month = $monthYear[0];
            $year = $monthYear[1];

            $formattedRecords[] = [
                'month' => $month,
                'year' => $year,
                'records' => $records->map(function ($record) {
                    return [
                        'temperature' => $record->temperature,
                        'status' => $record->status,
                        'checkup_time' => $record->checkup_time->format('d-m-Y'),
                    ];
                }),
            ];
        }

        return response()->json([
            'status' => 'sukses',
            'data' => $formattedRecords,
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
                'status' => 'required|in:sehat,sakit',
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

    // Di dalam HealthRecordControllerApi
    public function showMonthlyHealthRecords($id)
    {
        $cattle = Cattle::find($id);

        // Cek apakah sapi ditemukan
        if (!$cattle) {
            return response()->json(['error' => 'Sapi tidak ditemukan'], 404);
        }

        // Ambil semua health record berdasarkan bulan dan tahun
        $healthRecords = HealthRecord::where('cattle_id', $id)
            ->orderBy('checkup_time', 'desc') // Urutkan berdasarkan tanggal
            ->get();

        $groupedRecords = $healthRecords->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->checkup_time)->format('F Y'); // Grup berdasarkan bulan dan tahun
        });

        $formattedRecords = [];

        foreach ($groupedRecords as $monthYear => $records) {
            $formattedRecords[] = [
                'month_year' => $monthYear,
                'records' => $records->map(function($record) {
                    return [
                        'checkup_time' => \Carbon\Carbon::parse($record->checkup_time)->format('d-m-Y'), // Format tanggal
                        'temperature' => $record->temperature ? $record->temperature . '℃' : 'N/A',
                        'status' => $record->status,
                    ];
                })
            ];
        }

        return response()->json([
            'status' => 'sukses',
            'cattle' => [
                'id' => $cattle->id,
                'name' => $cattle->name,
                'species' => $cattle->species,
                'gender' => $cattle->gender,
                'health_records' => $formattedRecords
            ]
        ]);
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
            'status' => 'nullable|in:sehat,sakit',
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
