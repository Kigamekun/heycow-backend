<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cattle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class CattleControllerApi extends Controller
{
    public function index()
    {
        $cattles = Cattle::latest()->get();
        return response()->json($cattles);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'breed' => 'required',
            'status' => 'required|in:alive,dead,sold',
            'birth_date' => 'required|date',
            'birth_weight' => 'required|numeric',
            'farm_id' => 'required|exists:farms,id',
            'user_id' => 'required|exists:users,id',
            'iot_device_id' => 'required|exists:iot_devices,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cattle = Cattle::create($request->all());
        return response()->json($cattle, 201);
    }

    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $cattle = Cattle::find($id);

        if (!$cattle) {
            return response()->json(['message' => 'Cattle not found'], 404);
        }

        return response()->json($cattle);
    }

    public function update(Request $request, $id)
{
    // $id = Crypt::decrypt($id);
    $cattle = Cattle::find($id);

    if (!$cattle) {
        return response()->json(['message' => 'Cattle not found'], 404);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'breed' => 'required|string|max:255',
        'status' => 'required|in:alive,dead,sold',
        'birth_date' => 'required|date',
        'birth_weight' => 'required|numeric',
        'farm_id' => 'required|exists:farms,id',
        'user_id' => 'required|exists:users,id',
        'iot_device_id' => 'required|exists:iot_devices,id',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $cattleData = $request->only([
        'name',
        'breed',
        'status',
        'birth_date',
        'birth_weight',
        'farm_id',
        'user_id',
        'iot_device_id'
    ]);

    $cattle->update($cattleData);
    return response()->json($cattle);
}


    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $cattle = Cattle::find($id);

        if (!$cattle) {
            return response()->json(['message' => 'Cattle not found'], 404);
        }

        $cattle->delete();
        return response()->json(['message' => 'Cattle deleted successfully']);
    }
}
