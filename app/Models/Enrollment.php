<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $primaryKey = 'enrollment_id';
    
    // Mapping timestamp kustom
    public const CREATED_AT = 'enrolled_at';
    public const UPDATED_AT = null; // Tidak ada 'updated_at'

    /**
     * Relasi: Satu Enrollment milik satu User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relasi: Satu Enrollment milik satu Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Relasi: Satu Enrollment memiliki banyak data LessonCompletion
     */
    public function lessonCompletions()
    {
        return $this->hasMany(LessonCompletion::class, 'enrollment_id', 'enrollment_id');
    }
}