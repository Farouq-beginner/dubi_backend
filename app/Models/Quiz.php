<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    // Nama tabel ini jamak (quizzes), jadi sudah sesuai standar
    protected $primaryKey = 'quiz_id';
    public $timestamps = false; // Tidak punya timestamp

    protected $fillable = [
        'course_id',
        'module_id',
        'title',
        'description',
    ];

    /**
     * Relasi: Satu Quiz milik satu Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Relasi: Satu Quiz bisa jadi milik satu Module (nullable)
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'module_id');
    }

    /**
     * Relasi: Satu Quiz memiliki banyak Question
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'quiz_id', 'quiz_id');
    }

    /**
     * Relasi: Satu Quiz memiliki banyak Attempt (percobaan)
     */
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id', 'quiz_id');
    }
}