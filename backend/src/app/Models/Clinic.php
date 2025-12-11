<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'timezone', 'phone', 'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
