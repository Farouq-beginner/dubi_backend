<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\UserQuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Mengambil detail Kuis SPESIFIK (pertanyaan & pilihan jawaban).
     * Kita sembunyikan 'is_correct' dari pilihan jawaban.
     */
    public function show(Quiz $quiz)
    {
        $quiz->load(['questions.answers' => function ($query) {
            // Kita HANYA pilih kolom yang kita mau
            // dan sembunyikan 'is_correct' dari siswa
            $query->select('answer_id', 'question_id', 'answer_text');
        }]);

        return response()->json([
            'success' => true,
            'data' => $quiz
        ]);
    }

    /**
     * Menerima jawaban kuis dari siswa, menghitung skor,
     * dan menyimpan riwayat pengerjaan.
     */
    public function submit(Request $request, Quiz $quiz)
    {
        $user = Auth::user();

        // 1. Validasi input (harus berupa array jawaban)
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,question_id',
            'answers.*.answer_id' => 'required|integer|exists:answers,answer_id',
        ]);

        $submittedAnswers = $validated['answers'];
        $totalQuestions = $quiz->questions()->count();
        $correctAnswersCount = 0;

        // Kita gunakan DB Transaction untuk memastikan semua data tersimpan
        // atau tidak sama sekali jika ada error.
        DB::beginTransaction();
        try {
            // 2. Buat Quiz Attempt (catatan pengerjaan)
            $attempt = QuizAttempt::create([
                'user_id' => $user->user_id,
                'quiz_id' => $quiz->quiz_id,
                'score' => 0, // Skor awal
            ]);

            // 3. Ambil semua ID jawaban yang benar untuk kuis ini
            $correctAnswers = DB::table('answers')
                ->whereIn('question_id', array_column($submittedAnswers, 'question_id'))
                ->where('is_correct', 1)
                ->pluck('answer_id', 'question_id'); // [question_id => correct_answer_id]

            // 4. Periksa setiap jawaban siswa
            foreach ($submittedAnswers as $answer) {
                $questionId = $answer['question_id'];
                $selectedAnswerId = $answer['answer_id'];

                // Simpan jawaban siswa
                UserQuizAnswer::create([
                    'attempt_id' => $attempt->attempt_id,
                    'question_id' => $questionId,
                    'selected_answer_id' => $selectedAnswerId,
                ]);

                // Cek apakah jawaban benar
                if (isset($correctAnswers[$questionId]) && $correctAnswers[$questionId] == $selectedAnswerId) {
                    $correctAnswersCount++;
                }
            }

            // 5. Hitung skor
            $score = ($totalQuestions > 0) ? ($correctAnswersCount / $totalQuestions) * 100 : 0;

            // 6. Update skor di attempt
            $attempt->update([
                'score' => $score,
                'completed_at' => now(), // Tandai sebagai selesai
            ]);

            // 7. Simpan semua perubahan ke DB
            DB::commit();

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
            DB::rollBack(); // Batalkan semua jika ada error
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan kuis: ' . $e->getMessage()
            ], 500);
        }
    }
}