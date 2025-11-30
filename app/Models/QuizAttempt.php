<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $primaryKey = 'attempt_id';

    // Aktifkan timestamp Laravel
    public $timestamps = true;

    // Mapping timestamp custom
    public const CREATED_AT = 'started_at';
    public const UPDATED_AT = 'completed_at';

    protected $fillable = [
        'user_id',
        'quiz_id',
        'score'
        // completed_at diisi otomatis oleh UPDATED_AT
    ];

    /** Relasi: Attempt -> User */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /** Relasi: Attempt -> Quiz */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    /** Relasi: Attempt -> UserQuizAnswer (jawaban user) */
    public function userAnswers()
    {
        return $this->hasMany(UserQuizAnswer::class, 'attempt_id', 'attempt_id');
    }
}
