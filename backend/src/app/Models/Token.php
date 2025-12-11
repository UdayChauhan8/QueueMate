<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id','service_id','token_number','customer_name','customer_phone_encrypted','status','estimated_wait','meta','called_at','served_at','cancelled_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
