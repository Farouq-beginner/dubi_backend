<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Untuk debugging

class ProgressController extends Controller
{
    public function getProgress(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Hanya untuk siswa.'], Response::HTTP_FORBIDDEN);
        }

        try {
            // 1. Ambil Riwayat Kuis (Skor Tertinggi per Kuis)
            $quizHistory = QuizAttempt::where('user_id', $user->user_id)
                ->with('quiz:quiz_id,title') // Ambil judul kuis
                ->select('quiz_id', DB::raw('MAX(score) as high_score')) // Ambil skor tertinggi
                ->groupBy('quiz_id')
                ->orderBy('quiz_id', 'desc')
                ->get();

            // 2. Ambil Progres Kursus (Enrollment & Persentase Materi Selesai)
            $enrollments = Enrollment::where('user_id', $user->user_id)
                ->with('course:course_id,title') // Ambil judul kursus
                ->get();
                
            $courseProgress = [];
            foreach ($enrollments as $enrollment) {
                
                // --- [PERBAIKAN DI SINI] ---
                // Cek jika kursus (course) terkait masih ada
                if ($enrollment->course == null) {
                    continue; // Lewati enrollment ini jika kursusnya sudah dihapus
                }
                // ---------------------------

                // Hitung total materi
                $totalLessons = DB::table('modules')
                    ->join('lessons', 'modules.module_id', '=', 'lessons.module_id')
                    ->where('modules.course_id', $enrollment->course_id)
                    ->count();

                // Hitung materi selesai
                $completedLessons = $enrollment->lessonCompletions()->count();

                $progressPercentage = ($totalLessons > 0) 
                    ? round(($completedLessons / $totalLessons) * 100) 
                    : 0;

                $courseProgress[] = [
                    'course_id' => $enrollment->course_id,
                    'course_title' => $enrollment->course->title,
                    'completed_lessons' => $completedLessons,
                    'total_lessons' => $totalLessons,
                    'progress_percentage' => $progressPercentage,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user_name' => $user->full_name,
                    'quiz_history' => $quizHistory,
                    'course_progress' => $courseProgress,
                ]
            ]);

        } catch (\Exception $e) {
            // Jika terjadi error SQL atau lainnya
            Log::error("Get Student Progress FAILED: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data progres: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}