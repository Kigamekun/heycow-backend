<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
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
        'snap_token',
        'transaction_time',
        'payment_type',
        'payment_status_message',
        'transaction_id',
        'jumlah_pembayaran',
        'created_at',
        'updated_at',
    ];

    public function request()
    {
        return $this->belongsTo(RequestAngon::class, 'request_id');
    }

    public function cattle()
    {
        return $this->belongsTo(Cattle::class, 'cattle_id');
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }


}
