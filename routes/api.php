<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'EDUSMART-CM API opérationnelle',
            'data' => [
                'service' => 'backend',
                'version' => '1.0.0',
            ],
        ]);
    });
});
