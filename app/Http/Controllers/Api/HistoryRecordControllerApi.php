<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\HistoryRecord;
use Illuminate\Http\Request;

class HistoryRecordControllerApi extends Controller
{

    public function index(){
        $devices = DB::table('history')->where('user_id',auth()->user()->id)->get();

        return response()->json([
            'message' => 'Data History',
            'status' => 'success',
            'data' => $devices
        ],200);

    }
    // Fungsi untuk mendapatkan semua riwayat perubahan berdasarkan cattle_id
    public function getHistoryByCattleId($cattle_id)
    {
        $historyRecord = DB::table('history_records')->where('id', $id)->first();

        // Decode JSON fields to objects
        if ($historyRecord) {
            $historyRecord->old_value = json_decode($historyRecord->old_value, true);
            $historyRecord->new_value = json_decode($historyRecord->new_value, true);
        }
    
        return new JsonResponse($historyRecord);
    }

    // Fungsi untuk mendapatkan detail riwayat berdasarkan id
    public function getHistoryDetail($id)
    {
        $historyRecord = HistoryRecord::find($id);

        if (!$historyRecord) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        return response()->json($historyRecord);
    }
}
