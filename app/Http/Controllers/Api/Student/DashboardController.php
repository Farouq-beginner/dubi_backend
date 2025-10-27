<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getDashboard(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Hanya untuk siswa.'], Response::HTTP_FORBIDDEN);
        }

        try {
            // --- 1. STATISTIK KESELURUHAN ---
            
            // Rata-rata Nilai dari semua kuis
            $averageScore = $user->quizAttempts()->avg('score');
            
            // Jumlah kuis yang lulus (misal, skor > 70)
            $quizzesPassed = $user->quizAttempts()->where('score', '>=', 70)->count();

            // --- 2. PROGRES KURSUS (MATERI) ---
            $enrollments = Enrollment::where('user_id', $user->user_id)
                ->with('course:course_id,title')
                ->get();
                
            $courseProgress = [];
            $coursesCompleted = 0;

            foreach ($enrollments as $enrollment) {
                if ($enrollment->course == null) continue; // Kursus mungkin terhapus

                $totalLessons = DB::table('modules')
                    ->join('lessons', 'modules.module_id', '=', 'lessons.module_id')
                    ->where('modules.course_id', $enrollment->course_id)
                    ->count();

                $completedLessons = $enrollment->lessonCompletions()->count();

                $progressPercentage = ($totalLessons > 0) 
                    ? round(($completedLessons / $totalLessons) * 100) 
                    : 0;

                if ($progressPercentage >= 100) {
                    $coursesCompleted++;
                }

                $courseProgress[] = [
                    'course_id' => $enrollment->course_id,
                    'course_title' => $enrollment->course->title,
                    'completed_lessons' => $completedLessons,
                    'total_lessons' => $totalLessons,
                    'progress_percentage' => $progressPercentage,
                ];
            }
            
            // --- 3. RIWAYAT KUIS TERBARU (5 TERAKHIR) ---
            $recentQuizHistory = $user->quizAttempts()
                ->with('quiz:quiz_id,title')
                ->select('attempt_id', 'quiz_id', 'score', 'completed_at')
                ->whereNotNull('completed_at') // Hanya ambil yang sudah selesai
                ->orderBy('completed_at', 'desc')
                ->limit(5)
                ->get();


            return response()->json([
                'success' => true,
                'data' => [
                    'user_name' => $user->full_name,
                    'statistics' => [
                        'courses_completed' => $coursesCompleted,
                        'quizzes_passed' => $quizzesPassed,
                        'average_score' => round($averageScore, 2), // Rata-rata 2 desimal
                        // 'streak' => 0, // Placeholder
                        // 'total_hours' => 0, // Placeholder
                    ],
                    'course_progress' => $courseProgress,
                    'recent_quiz_history' => $recentQuizHistory,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Get Student Dashboard FAILED: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dashboard: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}