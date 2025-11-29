<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\UserQuizAnswer;
use App\Models\Notification; // <-- [PENTING] Import Model Notification
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        $quiz->load(['questions.answers' => function ($query) {
            $query->select('answer_id', 'question_id', 'answer_text');
        }]);

        return response()->json([
            'success' => true,
            'data' => $quiz
        ]);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,question_id',
            'answers.*.answer_id' => 'required|integer|exists:answers,answer_id',
        ]);

        $submittedAnswers = $validated['answers'];
        $totalQuestions = $quiz->questions()->count();
        $correctAnswersCount = 0;

        DB::beginTransaction();
        try {
            // 1. Buat Quiz Attempt
            $attempt = QuizAttempt::create([
                'user_id' => $user->user_id,
                'quiz_id' => $quiz->quiz_id,
                'score' => 0,
            ]);

            // 2. Cek Jawaban Benar
            $correctAnswers = DB::table('answers')
                ->whereIn('question_id', array_column($submittedAnswers, 'question_id'))
                ->where('is_correct', 1)
                ->pluck('answer_id', 'question_id');

            // 3. Simpan Jawaban User
            foreach ($submittedAnswers as $answer) {
                $questionId = $answer['question_id'];
                $selectedAnswerId = $answer['answer_id'];

                UserQuizAnswer::create([
                    'attempt_id' => $attempt->attempt_id,
                    'question_id' => $questionId,
                    'selected_answer_id' => $selectedAnswerId,
                ]);

                if (isset($correctAnswers[$questionId]) && $correctAnswers[$questionId] == $selectedAnswerId) {
                    $correctAnswersCount++;
                }
            }

            // 4. Hitung Skor
            $score = ($totalQuestions > 0) ? ($correctAnswersCount / $totalQuestions) * 100 : 0;
            
            // Format skor agar rapi (misal: 85.5)
            $formattedScore = number_format($score, 1);

            // 5. Update Attempt
            $attempt->update([
                'score' => $score,
                'completed_at' => now(),
            ]);

            // --- [FITUR BARU] BUAT NOTIFIKASI ---
            Notification::create([
                'user_id' => $user->user_id,
                'title' => 'Kuis Selesai',
                // Gunakan nama kuis yang dinamis dan skor yang diformat
                'body' => "Selamat! Anda telah menyelesaikan kuis '{$quiz->title}' dengan nilai {$formattedScore}.",
                'type' => $score >= 70 ? 'success' : 'info', // Hijau jika lulus, Biru jika tidak
                'is_read' => 0
            ]);
            // ------------------------------------

            DB::commit(); // Simpan semua (termasuk notifikasi)

            return response()->json([
                'success' => true,
                'message' => 'Kuis berhasil diselesaikan!',
                'data' => [
                    'score' => $score,
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswersCount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan kuis: ' . $e->getMessage()
            ], 500);
        }
    }
}