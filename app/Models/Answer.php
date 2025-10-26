<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $primaryKey = 'answer_id';
    public $timestamps = false; // Tidak punya timestamp
    protected $fillable = ['question_id', 'answer_text', 'is_correct'];

    /**
     * Relasi: Satu Answer (pilihan jawaban) milik satu Question
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }
}