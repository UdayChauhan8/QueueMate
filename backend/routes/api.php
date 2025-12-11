<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\JoinController;

Route::prefix('api/v1')->group(function () {
    Route::post('/clinics/{clinic_slug}/join', [JoinController::class, 'join']);
    Route::get('/clinics/{clinic_slug}/status/{token_id}', [JoinController::class, 'status']);
});
