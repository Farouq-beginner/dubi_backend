<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonCompletion extends Model
{
    use HasFactory;
    
    // Nama tabel ini tidak standar (plural), jadi kita tentukan manual
    protected $table = 'lesson_completion';
    
    protected $primaryKey = 'completion_id';
    
    // Mapping timestamp kustom
    public const CREATED_AT = 'completed_at';
    public const UPDATED_AT = null; // Tidak ada 'updated_at'

    /**
     * Relasi: Satu Completion milik satu Enrollment
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id', 'enrollment_id');
    }

    /**
     * Relasi: Satu Completion milik satu Lesson
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id', 'lesson_id');
    }
}