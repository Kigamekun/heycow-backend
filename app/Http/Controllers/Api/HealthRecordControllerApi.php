<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use Illuminate\Http\Request;

class HealthRecordControllerApi extends Controller
{
    public function index()
    {
        return HealthRecord::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cattle_id' => 'required|bigint',
            'checkup_time' => 'required|date',
            'temperature' => 'required|numeric',
            'heart_rate' => 'required|integer',
            'status' => 'required|in:sick,healthy',
            'weight' => 'nullable|numeric',
            'veterinarian' => 'nullable|string|max:255',
        ]);

        $healthRecord = HealthRecord::create($validatedData);
        return response()->json($healthRecord, 201);
    }

    public function show($id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        return response()->json($healthRecord);
    }

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
        return response()->json($healthRecord);
    }

    public function destroy($id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        $healthRecord->delete();
        return response()->json(null, 204);
    }
}
