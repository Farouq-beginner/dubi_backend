<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'course_id';
    
    // Hanya punya 'created_at', tidak punya 'updated_at'
    public const UPDATED_AT = null;

    // Otomatis load relasi ini saat query Course (sesuai yang kita buat sebelumnya)
    protected $with = ['level', 'subject', 'creator'];

    /**
     * Relasi: Satu Course milik satu Level
     */

    protected $fillable = [
        'title',
        'description',
        'level_id',
        'subject_id',
        'created_by_user_id',
    ];
    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }

    /**
     * Relasi: Satu Course milik satu Subject
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    /**
     * Relasi: Satu Course dibuat oleh satu User (teacher/admin)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'user_id');
    }

    /**
     * Relasi: Satu Course memiliki banyak Module
     */
    public function modules()
    {
        return $this->hasMany(Module::class, 'course_id', 'course_id');
    }

    /**
     * Relasi: Satu Course memiliki banyak Quiz
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'course_id', 'course_id');
    }

    /**
     * Relasi: Satu Course memiliki banyak Enrollment (pendaftaran)
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_id', 'course_id');
    }
}