<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    // 'request_id' => $requestNgangon->id,
    // 'cattle_id' => $requestNgangon->cattle_id,
    // 'start_date' => now(),
    // 'end_date' => now()->addDays($requestNgangon->duration),
    // 'rate' => 10,
    // 'initial_weight' => $requestNgangon->cattle->weight,
    // 'initial_height' => $requestNgangon->cattle->height,
    // 'status' => 'active',

    protected $table = 'contracts';

    protected $fillable = [
        'request_id',
        'cattle_id',
        'start_date',
        'end_date',
        'rate',
        'farm_id',
        'initial_weight',
        'initial_height',
        'status',
        'created_at',
        'updated_at',
    ];
}
