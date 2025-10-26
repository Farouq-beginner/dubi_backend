<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log; 
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; 

class QuizController extends Controller
{
    /**
     * Menyimpan (membuat) Kuis baru ke Course.
     */
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        if ($user->role !== 'teacher' || $user->user_id !== $course->created_by_user_id) {
            return response()->json(['message' => 'Hanya Guru yang diizinkan.'], Response::HTTP_FORBIDDEN);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module_id' => 'nullable|integer|exists:modules,module_id',
            'duration' => 'nullable|integer|min:0',
        ]);

        $quiz = $course->quizzes()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'module_id' => $validated['module_id'] ?? null,
            'duration' => $validated['duration'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kuis baru berhasil ditambahkan!',
            'data' => $quiz
        ], Response::HTTP_CREATED);
    }

    /**
     * Menampilkan detail kuis LENGKAP untuk Guru (termasuk 'is_correct').
     */
    public function show(Quiz $quiz)
    {
        $user = Auth::user();
        if ($user->role !== 'teacher' || $user->user_id !== $quiz->course->created_by_user_id) {
             return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        $quiz->load('questions.answers'); 
        return response()->json(['success' => true, 'data' => $quiz]);
    }

    /**
     * [FUNGSI YANG HILANG] Mengubah detail Kuis yang sudah ada.
     */
    public function update(Request $request, Course $course, Quiz $quiz)
    {
        $user = Auth::user();

        // 1. Otorisasi Manual (Role & Kepemilikan)
        if ($user->role !== 'teacher' || $user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }
        
        // 2. Cek Asosiasi
        if ($quiz->course_id !== $course->course_id) {
             return response()->json(['message' => 'Kuis tidak ditemukan di kursus ini.'], Response::HTTP_NOT_FOUND);
        }

        // 3. Validasi
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module_id' => 'nullable|integer|exists:modules,module_id',
            'duration' => 'nullable|integer|min:0',
        ]);

        // 4. Update Data (Menggunakan set properti manual dan save untuk bypass cache/fillable)
        try {
            $durationValue = $validated['duration'];
            $finalDuration = ($durationValue === '' || $durationValue === null) ? null : (int)$durationValue;

            // Set properti Model secara manual
            $quiz->title = $validated['title'];
            $quiz->description = $validated['description'] ?? null;
            $quiz->module_id = $validated['module_id'] ?? null;
            $quiz->duration = $finalDuration; 
            
            $quiz->save(); // <-- Paksa simpan

            return response()->json([
                'success' => true, 
                'message' => 'Kuis berhasil diperbarui!' // Pesan ini akan muncul di Flutter
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error("QUIZ UPDATE CRASH: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal memperbarui kuis: Terjadi kesalahan server.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Menghapus Quiz.
     */
    public function destroy(Course $course, Quiz $quiz)
    {
        $user = Auth::user();

        if ($user->role !== 'teacher' || $user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }
        
        if ($quiz->course_id !== $course->course_id) {
             return response()->json(['message' => 'Kuis tidak ditemukan di kursus ini.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $quiz->delete();
            return response()->json(['success' => true, 'message' => 'Kuis berhasil dihapus!'], Response::HTTP_OK);
        } catch (\Exception $e) {
             Log::error("QUIZ DELETE CRASH: " . $e->getMessage()); 
             return response()->json([
                 'success' => false, 
                 'message' => 'Gagal menghapus kuis: Terdapat data siswa yang terkait.'
             ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}