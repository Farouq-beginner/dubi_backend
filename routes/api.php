<?php

use App\Http\Controllers\UpdateController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\BrowseController;
use App\Http\Controllers\Api\SempoaController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ServerController;
// --- CONTROLLER GURU ---
use App\Http\Controllers\Api\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Api\Teacher\ModuleController as TeacherModuleController;
use App\Http\Controllers\Api\Teacher\LessonController as TeacherLessonController;
use App\Http\Controllers\Api\Teacher\QuizController as TeacherQuizController;
use App\Http\Controllers\Api\Teacher\QuestionController as TeacherQuestionController;
// --- CONTROLLER ADMIN ---
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Api\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Api\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Api\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Api\Admin\QuestionController as AdminQuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- RUTE PUBLIK ---
Route::get('/server/status', [ServerController::class, 'checkStatus']);

Route::get('/check-update', [UpdateController::class, 'checkUpdate']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/levels', [LevelController::class, 'index']);
Route::get('/subjects', [SubjectController::class, 'index']);
Route::get('/courses/level/{level}', [CourseController::class, 'getCoursesByLevel']);
Route::get('/courses/subject/{subject}', [CourseController::class, 'getCoursesBySubject']);
Route::get('/browse/courses', [BrowseController::class, 'getAllCoursesByLevel']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ...existing code...
Route::middleware('auth:sanctum')->group(function () {

    // --- PROTECTED ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/server/session', [ServerController::class, 'checkSession']);
        Route::post('/auth/logout-others', [ServerController::class, 'logoutOtherDevices']);
        // ...
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // --- [BARU] RUTE PROFIL PENGGUNA ---
    // Route untuk upload foto (method POST karena mengirim file)
    Route::post('/profile/update-photo', [ProfileController::class, 'updatePhoto']);

    // Route untuk update data teks (nama/email) (method PUT untuk update)
    Route::put('/profile/update', [ProfileController::class, 'updateProfile']);

    Route::post('/profile/send-password-code', [ProfileController::class, 'sendPasswordCode']);
    Route::post('/profile/reset-password-with-code', [ProfileController::class, 'resetPasswordWithCode']);

    // --- [BARU] RUTE SEMPOA ---
    Route::get('/sempoa/progress', [SempoaController::class, 'getProgress']);
    Route::post('/sempoa/progress', [SempoaController::class, 'saveProgress']);
    // [BARU] Rute Leaderboard
    Route::get('/sempoa/leaderboard', [SempoaController::class, 'getLeaderboard']);

    // --- Rute Umum & Siswa ---
    Route::get('/home/my-courses', [HomeController::class, 'myCourses']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);
    Route::get('/student/dashboard', [App\Http\Controllers\Api\Student\DashboardController::class, 'getDashboard']);

    // --- RUTE GURU ---
    Route::prefix('teacher')->group(function () {
        Route::post('/courses', [TeacherCourseController::class, 'store']);
        Route::put('/courses/{course}', [TeacherCourseController::class, 'update']);
        Route::delete('/courses/{course}', [TeacherCourseController::class, 'destroy']);

        Route::post('/courses/{course}/modules', [TeacherModuleController::class, 'store']);
        Route::put('/courses/{course}/modules/{module}', [TeacherModuleController::class, 'update']);
        Route::delete('/courses/{course}/modules/{module}', [TeacherModuleController::class, 'destroy']);

        Route::post('/modules/{module}/lessons', [TeacherLessonController::class, 'store']);
        Route::put('/modules/{module}/lessons/{lesson}', [TeacherLessonController::class, 'update']);
        Route::delete('/modules/{module}/lessons/{lesson}', [TeacherLessonController::class, 'destroy']);

        Route::post('/courses/{course}/quizzes', [TeacherQuizController::class, 'store']);
        Route::get('/quizzes/{quiz}', [TeacherQuizController::class, 'show']);
        Route::put('/courses/{course}/quizzes/{quiz}', [TeacherQuizController::class, 'update']);
        Route::delete('/courses/{course}/quizzes/{quiz}', [TeacherQuizController::class, 'destroy']);

        Route::post('/quizzes/{quiz}/questions', [TeacherQuestionController::class, 'store']);
        Route::put('/quizzes/{quiz}/questions/{question}', [TeacherQuestionController::class, 'update']);
        Route::delete('/quizzes/{quiz}/questions/{question}', [TeacherQuestionController::class, 'destroy']);
    });

    // --- RUTE KHUSUS ADMIN ---
    Route::prefix('admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

        // Fitur 5: Global Content Management (pindahkan ke dalam prefix admin)
        Route::put('/courses/{course}', [AdminCourseController::class, 'update']);
        Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy']);

        Route::put('/modules/{module}', [AdminModuleController::class, 'update']);
        Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy']);

        Route::put('/lessons/{lesson}', [AdminLessonController::class, 'update']);
        Route::delete('/lessons/{lesson}', [AdminLessonController::class, 'destroy']);

        Route::put('/quizzes/{quiz}', [AdminQuizController::class, 'update']);
        Route::delete('/quizzes/{quiz}', [AdminQuizController::class, 'destroy']);

        Route::put('/questions/{question}', [AdminQuestionController::class, 'update']);
        Route::delete('/questions/{question}', [AdminQuestionController::class, 'destroy']);
    });
});
// ...existing code...