<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class QuestionController extends Controller
{
    /**
     * Menyimpan Pertanyaan baru DAN pilihan jawabannya ke Kuis.
     */
    public function store(Request $request, Quiz $quiz)
    {
        $user = Auth::user();
        $course = $quiz->course; // Ambil course dari quiz

        // 1. OTORISASI MANUAL: Cek Role Guru
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Hanya Guru yang diizinkan untuk menambah pertanyaan.'], Response::HTTP_FORBIDDEN);
        }

        // 2. OTORISASI MANUAL: Cek Kepemilikan Kursus (melalui Course pemilik Quiz)
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit kuis ini.'], Response::HTTP_FORBIDDEN);
        }
        
        // 3. VALIDASI
        $validated = $request->validate([
            'question_text' => 'required|string|max:1000',
            'question_type' => ['required', Rule::in(['multiple_choice', 'true_false'])],
            
            // Validasi untuk pilihan jawaban (Answers)
            'answers' => 'required|array|min:2', 
            'answers.*.answer_text' => 'required|string|max:255',
            'answers.*.is_correct' => 'required|boolean',
            
            // Pastikan minimal ada satu jawaban yang benar
            'answers' => ['required', function ($attribute, $value, $fail) {
                $correctCount = 0;
                foreach ($value as $answer) {
                    if ($answer['is_correct']) {
                        $correctCount++;
                    }
                }
                if ($correctCount === 0) {
                    $fail('Minimal harus ada 1 jawaban yang benar.');
                }
            }],
        ]);

        // 4. SIMPAN KE DATABASE (Gunakan Transaction)
        DB::beginTransaction();
        try {
            // Buat Pertanyaan (Question)
            $order = $quiz->questions()->max('order_index') + 1;
            $question = $quiz->questions()->create([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'order_index' => $order
            ]);

            // Buat Pilihan Jawaban (Answers)
            foreach ($validated['answers'] as $answerData) {
                $question->answers()->create([
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct']
                ]);
            }

            DB::commit(); 

            // Muat relasi baru untuk dikirim balik
            $question->load('answers');

            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan baru berhasil ditambahkan!',
                'data' => $question
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pertanyaan: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Quiz $quiz, Question $question)
    {
        $user = Auth::user();
        $course = $quiz->course;

        // 1. Otorisasi Manual: Cek Role Guru
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        // 2. Otorisasi Manual: Cek Kepemilikan Course
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda bukan pemilik kuis ini.'], Response::HTTP_FORBIDDEN);
        }

        // 3. Cek Asosiasi: Pastikan Question ini milik Quiz yang benar
        if ($question->quiz_id !== $quiz->quiz_id) {
             return response()->json(['message' => 'Pertanyaan tidak ditemukan di Kuis ini.'], Response::HTTP_NOT_FOUND);
        }

        // 4. Hapus
        $question->delete();

        return response()->json(['success' => true, 'message' => 'Pertanyaan berhasil dihapus!']);
    }
}