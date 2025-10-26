<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class QuizController extends Controller
{
    /**
     * Menyimpan (membuat) Kuis baru ke Course.
     */
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        // 1. OTORISASI MANUAL: Cek Role Guru
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Hanya Guru yang diizinkan untuk membuat kuis.'], Response::HTTP_FORBIDDEN);
        }

        // 2. OTORISASI MANUAL: Cek Kepemilikan Kursus
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda tidak memiliki izin untuk menambah kuis di kursus ini.'], Response::HTTP_FORBIDDEN);
        }
        
        // 3. VALIDASI
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module_id' => 'nullable|integer|exists:modules,module_id', // Opsional
        ]);

        // 4. BUAT DATA KUIS
        $quiz = $course->quizzes()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'module_id' => $validated['module_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kuis baru berhasil ditambahkan!',
            'data' => $quiz // Mengembalikan objek Quiz yang baru dibuat
        ], Response::HTTP_CREATED);
    }

    public function destroy(Course $course, Quiz $quiz)
    {
        $user = Auth::user();

        // 1. OTORISASI MANUAL: Cek Role Guru
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Akses ditolak. (Hanya Guru)'], Response::HTTP_FORBIDDEN);
        }

        // 2. OTORISASI MANUAL: Cek Kepemilikan Course
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda bukan pemilik kursus ini.'], Response::HTTP_FORBIDDEN);
        }
        
        // 3. Cek Asosiasi: Pastikan Quiz ini milik Course yang benar
        if ($quiz->course_id !== $course->course_id) {
             return response()->json(['message' => 'Kuis tidak ditemukan di kursus ini.'], Response::HTTP_NOT_FOUND);
        }


        // 4. Hapus (Menggunakan try-catch untuk 500 Internal Server Error)
        try {
            // Ini akan memicu ON DELETE CASCADE di database
            $quiz->delete();
            
            return response()->json(['success' => true, 'message' => 'Kuis berhasil dihapus!'], Response::HTTP_OK);
            
        } catch (\Exception $e) {
             // Log error spesifik di sisi server (untuk diagnosis lebih lanjut)
             Log::error("QUIZ DELETE CRASH: " . $e->getMessage()); 
             
             // Kembalikan respons 500 yang jelas ke frontend
             return response()->json([
                 'success' => false, 
                 'message' => 'Gagal menghapus kuis: Terdapat data siswa yang terkait atau kendala database.'
             ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}