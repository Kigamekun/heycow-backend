<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestNgangon extends Model
{
    protected $table = 'request_ngangons';
    protected $fillable = [
        'cattle_id',
        'farm_id',
        'status',
        'user_id',
        'start_date',
        'end_date',
        'duration',
        'price' ,
        'note',
        'created_at',
        'updated_at',
    ];

    public function cattle()
    {
        return $this->belongsTo(Cattle::class, 'cattle_id', 'id');
    }
}
