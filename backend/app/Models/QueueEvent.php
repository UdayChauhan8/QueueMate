<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueEvent extends Model
{
    public $timestamps = false;
    protected $fillable = ['clinic_id','source','action','token_id','payload','confidence','created_at'];
    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];
}
