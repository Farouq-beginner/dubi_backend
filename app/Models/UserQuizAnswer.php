<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizAnswer extends Model
{
    use HasFactory;
    
    // Nama tabel ini jamak (user_quiz_answers), sudah sesuai standar
    protected $primaryKey = 'user_answer_id';
    public $timestamps = false; // Tidak punya timestamp

    /**
     * Relasi: Satu jawaban user milik satu Attempt
     */
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id', 'attempt_id');
    }

    /**
     * Relasi: Satu jawaban user merujuk ke satu Question
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    /**
     * Relasi: Satu jawaban user merujuk ke satu Answer (jika Pilihan Ganda)
     */
    public function selectedAnswer()
    {
        return $this->belongsTo(Answer::class, 'selected_answer_id', 'answer_id');
    }
}