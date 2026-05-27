<?php

use App\Http\Controllers\Api\Admin\ClassroomController;
use App\Http\Controllers\Api\Admin\StudentController;
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

    Route::prefix('admin')->group(function () {
        Route::apiResource('classrooms', ClassroomController::class);
        Route::apiResource('students', StudentController::class);
        Route::post('students/{student}/transfer', [StudentController::class, 'transfer'])->name('students.transfer');
        Route::post('students/{student}/expel', [StudentController::class, 'expel'])->name('students.expel');
    });
});
