<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;
    
    // Nama tabel ini jamak (quiz_attempts), sudah sesuai standar
    protected $primaryKey = 'attempt_id';

    // Mapping timestamp kustom
    public const CREATED_AT = 'started_at';
    public const UPDATED_AT = 'completed_at';

    /**
     * Relasi: Satu Attempt milik satu User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relasi: Satu Attempt milik satu Quiz
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    /**
     * Relasi: Satu Attempt memiliki banyak jawaban (UserQuizAnswer)
     */
    public function userAnswers()
    {
        return $this->hasMany(UserQuizAnswer::class, 'attempt_id', 'attempt_id');
    }
}