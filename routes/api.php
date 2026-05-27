<?php

use App\Http\Controllers\Api\Admin\BulletinController;
use App\Http\Controllers\Api\Admin\ClassroomController;
use App\Http\Controllers\Api\Admin\StudentController;
use App\Http\Controllers\Api\Admin\TeacherController;
use App\Http\Controllers\Api\Teacher\AbsenceController as TeacherAbsenceController;
use App\Http\Controllers\Api\Teacher\GradeController as TeacherGradeController;
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

        Route::apiResource('teachers', TeacherController::class);
        Route::post('teachers/{teacher}/assignments', [TeacherController::class, 'assignClassroom'])->name('teachers.assignments.store');
        Route::delete('teachers/{teacher}/assignments', [TeacherController::class, 'unassignClassroom'])->name('teachers.assignments.destroy');

        Route::get('students/{student}/bulletin', [BulletinController::class, 'showStudent'])->name('students.bulletin.show');
        Route::get('students/{student}/bulletin/pdf', [BulletinController::class, 'downloadStudentPdf'])->name('students.bulletin.pdf');
        Route::get('classrooms/{classroom}/bulletins', [BulletinController::class, 'showClassroom'])->name('classrooms.bulletins.index');
    });

    Route::prefix('teacher')->group(function () {
        Route::get('grades', [TeacherGradeController::class, 'index'])->name('teacher.grades.index');
        Route::post('grades/bulk', [TeacherGradeController::class, 'bulkStore'])->name('teacher.grades.bulk');

        Route::get('absences', [TeacherAbsenceController::class, 'index'])->name('teacher.absences.index');
        Route::post('absences/bulk', [TeacherAbsenceController::class, 'bulkStore'])->name('teacher.absences.bulk');
        Route::patch('absences/{absence}', [TeacherAbsenceController::class, 'update'])->name('teacher.absences.update');
    });
});
