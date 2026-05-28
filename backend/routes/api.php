<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\StudentController;
use App\Http\Controllers\Api\Admin\TeacherController;
use App\Http\Controllers\Api\Admin\ClassroomController;
use App\Http\Controllers\Api\Admin\AbsenceController;
use App\Http\Controllers\Api\Admin\SequenceController;
use App\Http\Controllers\Api\Admin\SubjectController;
use App\Http\Controllers\Api\Admin\BulletinController;
use App\Http\Controllers\Api\Admin\TimeTableController;

/*
|--------------------------------------------------------------------------
| API Routes — EduSmart CM
| Préfixe : /api/v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ---- AUTHENTIFICATION (publique) ----
    Route::post('/login', [AuthController::class, 'login']);

    // ---- ROUTES PROTÉGÉES (Sanctum) ----
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',     [AuthController::class, 'me']);

        // ---- ESPACE ADMIN ----
        Route::prefix('admin')->group(function () {

            // Dashboard
            Route::get('/dashboard/stats',          [DashboardController::class, 'stats']);
            Route::get('/dashboard/absences-stats', [DashboardController::class, 'absencesStats']);
            Route::get('/dashboard/grades-stats',   [DashboardController::class, 'gradesStats']);

            // Classes
            Route::get('/classrooms',          [ClassroomController::class, 'index']);
            Route::post('/classrooms',         [ClassroomController::class, 'store']);
            Route::put('/classrooms/{classroom}',    [ClassroomController::class, 'update']);
            Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy']);

            // Élèves
            Route::get('/students',                      [StudentController::class, 'index']);
            Route::post('/students',                     [StudentController::class, 'store']);
            Route::put('/students/{student}',            [StudentController::class, 'update']);
            Route::post('/students/{student}/transfer',  [StudentController::class, 'transfer']);
            Route::post('/students/{student}/expel',     [StudentController::class, 'expel']);
            Route::delete('/students/{student}',         [StudentController::class, 'destroy']);

            // Enseignants
            Route::get('/teachers',                                  [TeacherController::class, 'index']);
            Route::post('/teachers',                                 [TeacherController::class, 'store']);
            Route::put('/teachers/{teacher}',                        [TeacherController::class, 'update']);
            Route::post('/teachers/{teacher}/assign-classroom',      [TeacherController::class, 'assignClassroom']);
            Route::post('/teachers/{teacher}/unassign-classroom',    [TeacherController::class, 'unassignClassroom']);
            Route::delete('/teachers/{teacher}',                     [TeacherController::class, 'destroy']);

            // Absences
            Route::get('/absences',              [AbsenceController::class, 'index']);
            Route::post('/absences',             [AbsenceController::class, 'store']);
            Route::put('/absences/{absence}',    [AbsenceController::class, 'update']);
            Route::delete('/absences/{absence}', [AbsenceController::class, 'destroy']);

            // Séquences
            Route::get('/sequences',               [SequenceController::class, 'index']);
            Route::post('/sequences',              [SequenceController::class, 'store']);
            Route::put('/sequences/{sequence}',    [SequenceController::class, 'update']);
            Route::delete('/sequences/{sequence}', [SequenceController::class, 'destroy']);

            // Matières
            Route::get('/subjects',              [SubjectController::class, 'index']);
            Route::post('/subjects',             [SubjectController::class, 'store']);
            Route::put('/subjects/{subject}',    [SubjectController::class, 'update']);
            Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);

            // Bulletins
            Route::get('/bulletins',                                    [BulletinController::class, 'index']);
            Route::get('/bulletins/student/{student}/download',         [BulletinController::class, 'downloadStudent']);
            Route::get('/bulletins/classroom/{classroom}/download',     [BulletinController::class, 'downloadClassroom']);

            // Emplois du temps
            Route::get('/timetables',              [TimeTableController::class, 'index']);
            Route::post('/timetables',             [TimeTableController::class, 'store']);
            Route::put('/timetables/{timeTable}',  [TimeTableController::class, 'update']);
            Route::delete('/timetables/{timeTable}', [TimeTableController::class, 'destroy']);
        });
    });
});
