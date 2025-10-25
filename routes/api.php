<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\QuizController;
// --- TAMBAHKAN CONTROLLER GURU ---
use App\Http\Controllers\Api\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Api\Teacher\ModuleController; // <-- Tambah
use App\Http\Controllers\Api\Teacher\LessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Ini mungkin sudah ada jika Sanctum diinstal
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- TAMBAHKAN INI ---
// Rute kita: Mengambil kursus berdasarkan ID Jenjang
// GET /api/courses/level/{id_level_disini}
Route::get('/courses/level/{level}', [CourseController::class, 'getCoursesByLevel']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/levels', [App\Http\Controllers\Api\LevelController::class, 'index']);
Route::get('/subjects', [App\Http\Controllers\Api\SubjectController::class, 'index']);

// --- RUTE TERPROTEKSI (Harus login) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // --- [BARU] Rute untuk Home ---
    Route::get('/home/my-courses', [HomeController::class, 'myCourses']);

    // (Kita taruh di sini agar hanya user terdaftar yang bisa lihat detail)
    Route::get('/courses/{course}', [CourseController::class, 'show']);

    // --- [BARU] Rute untuk Kuis ---
    // Mengambil data pertanyaan kuis (untuk siswa)
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);

    // Mengirim jawaban kuis
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);



    // --- RUTE KHUSUS TEACHER ---
    // (can:is-teacher) -> Memanggil Gate 'is-teacher' yang kita buat
    Route::middleware('can:is-teacher')->prefix('teacher')->group(function () {

        Route::post('/courses', [TeacherCourseController::class, 'store']);

        // --- [BARU] Rute untuk Modul & Lesson ---
        // POST /api/teacher/courses/{course_id}/modules
        Route::post('/courses/{course}/modules', [ModuleController::class, 'store']);

        // POST /api/teacher/modules/{module_id}/lessons
        Route::post('/modules/{module}/lessons', [LessonController::class, 'store']);
    });



    // --- RUTE KHUSUS ADMIN ---
    // (can:is-admin) -> Memanggil Gate 'is-admin'
    Route::middleware('can:is-admin')->prefix('admin')->group(function () {
        // (Nanti kita tambahkan rute CRUD User di sini)
        // GET /api/admin/users
        // POST /api/admin/users
    });
});
