<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\QuizController;
// --- CONTROLLER GURU ---
use App\Http\Controllers\Api\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Api\Teacher\ModuleController as TeacherModuleController;
use App\Http\Controllers\Api\Teacher\LessonController as TeacherLessonController; // <-- Aliaskan untuk LessonController agar rapi
use App\Http\Controllers\Api\Teacher\QuizController as TeacherQuizController;
use App\Http\Controllers\Api\Teacher\QuestionController as TeacherQuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;


// --- RUTE PUBLIK ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/levels', [LevelController::class, 'index']);
Route::get('/subjects', [SubjectController::class, 'index']);
Route::get('/courses/level/{level}', [CourseController::class, 'getCoursesByLevel']);


// --- RUTE TERPROTEKSI (Harus login via Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Rute Umum (Siswa/Admin) ---
    Route::get('/home/my-courses', [HomeController::class, 'myCourses']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);


    // ---------------------------------------------------------------------
    // --- RUTE GURU (100% DIJAMIN MANUAL CHECK DI CONTROLLER) ---
    // Semua rute ini HANYA dilindungi oleh auth:sanctum.

    // 1. Buat Kursus
    Route::post('/teacher/courses', [TeacherCourseController::class, 'store']);

    // 2. Tambah Modul (FIXED)
    Route::post('/teacher/courses/{course}/modules', [TeacherModuleController::class, 'store']);

    // 3. Tambah Materi (PERLU ALIAS BARU AGAR TIDAK BINGUNG)
    Route::post('/teacher/modules/{module}/lessons', [TeacherLessonController::class, 'store']);

    // 4. Buat Kuis
    Route::post('/teacher/courses/{course}/quizzes', [TeacherQuizController::class, 'store']);

    // 5. Tambah Pertanyaan Kuis
    Route::post('/teacher/quizzes/{quiz}/questions', [TeacherQuestionController::class, 'store']);
    // ---------------------------------------------------------------------

    // 1. Course (Buat/Edit/Hapus)
    Route::post('/teacher/courses', [TeacherCourseController::class, 'store']);
    Route::put('/teacher/courses/{course}', [TeacherCourseController::class, 'update']); // <-- BARU
    Route::delete('/teacher/courses/{course}', [TeacherCourseController::class, 'destroy']); // <-- BARU

    // 2. Module (Buat/Edit/Hapus)
    Route::post('/teacher/courses/{course}/modules', [TeacherModuleController::class, 'store']);
    Route::put('/teacher/courses/{course}/modules/{module}', [TeacherModuleController::class, 'update']); // <-- HARUS ADA {course} & {module}
    Route::delete('/teacher/courses/{course}/modules/{module}', [TeacherModuleController::class, 'destroy']); // <-- HARUS ADA {course} & {module}
    // 3. Lesson (Buat/Edit/Hapus)
    Route::post('/teacher/modules/{module}/lessons', [TeacherLessonController::class, 'store']);
    Route::put('/teacher/modules/{module}/lessons/{lesson}', [TeacherLessonController::class, 'update']); // <-- BARU
    Route::delete('/teacher/modules/{module}/lessons/{lesson}', [TeacherLessonController::class, 'destroy']); // <-- BARU

    // 4. Quiz (Buat/Edit/Hapus)
    Route::post('/teacher/courses/{course}/quizzes', [TeacherQuizController::class, 'store']);
    Route::put('/teacher/courses/{course}/quizzes/{quiz}', [TeacherQuizController::class, 'update']);
    Route::delete('/teacher/courses/{course}/quizzes/{quiz}', [TeacherQuizController::class, 'destroy']);

    // 5. Question (Buat/Edit/Hapus)
    Route::post('/teacher/quizzes/{quiz}/questions', [TeacherQuestionController::class, 'store']);
    Route::put('/teacher/quizzes/{quiz}/questions/{question}', [TeacherQuestionController::class, 'update']); // <-- BARU
    Route::delete('/teacher/quizzes/{quiz}/questions/{question}', [TeacherQuestionController::class, 'destroy']); // <-- BARU

    // 6. [BARU] Rute GET Kuis untuk Guru (menampilkan jawaban benar)
    Route::get('/teacher/quizzes/{quiz}', [TeacherQuizController::class, 'show']);

    // --- RUTE KHUSUS ADMIN ---
    Route::middleware('can:is-admin')->prefix('admin')->group(function () {
        // ...
    });
});