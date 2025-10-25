<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $primaryKey = 'lesson_id';
    protected $fillable = ['title', 'content_type', 'content_body', 'order_index'];
    public $timestamps = false; // Tidak punya timestamp

    /**
     * Relasi: Satu Lesson milik satu Module
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'module_id');
    }

    /**
     * Relasi: Satu Lesson bisa diselesaikan (completion) berkali-kali oleh user
     */
    public function completions()
    {
        return $this->hasMany(LessonCompletion::class, 'lesson_id', 'lesson_id');
    }
}