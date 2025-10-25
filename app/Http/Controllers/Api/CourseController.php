<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course; // <-- Tambahkan ini
use App\Models\Level;  // <-- Tambahkan ini
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Mengambil semua kursus berdasarkan Level (Jenjang).
     */
    public function getCoursesByLevel(Level $level)
    {
        // 'Level $level' disebut "Route Model Binding".
        // Laravel akan otomatis mencari Level berdasarkan ID di URL.
        
        try {
            // Kita cari kursus berdasarkan level_id dari Level yang ditemukan
            // Kita tidak perlu memanggil ->with('level', 'subject') lagi
            // karena kita sudah menambahkannya di properti $with di Model Course
            $courses = Course::where('level_id', $level->level_id)->get();
            
            // Kembalikan data sebagai JSON
            return response()->json([
                'success' => true,
                'data' => $courses
            ]);

        } catch (\Exception $e) {
            // Jika terjadi error
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500); // 500 = Internal Server Error
        }
    }

    public function show(Course $course)
    {
        // 'Course $course' adalah Route Model Binding.
        // Kita panggil relasi 'modules' dan 'lessons' di dalam 'modules'
        $courseDetails = $course->load('modules.lessons', 'quizzes');

        return response()->json([
            'success' => true,
            'data' => $courseDetails
        ]);
    }
}