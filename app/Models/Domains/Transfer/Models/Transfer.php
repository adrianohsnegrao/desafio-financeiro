<?php

namespace App\Models\Domains\Transfer\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'payer_id',
        'payee_id',
        'amount',
        'status',
        'idempotency_key',
    ];
}
