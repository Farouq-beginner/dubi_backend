<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $primaryKey = 'question_id';
    public $timestamps = false; // Tidak punya timestamp

    /**
     * Relasi: Satu Question milik satu Quiz
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    /**
     * Relasi: Satu Question memiliki banyak Answer (pilihan jawaban)
     */
    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id', 'question_id');
    }
}