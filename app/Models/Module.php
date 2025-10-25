<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $primaryKey = 'module_id';
    protected $fillable = ['title', 'order_index'];
    public $timestamps = false; // Tidak punya timestamp

    /**
     * Relasi: Satu Module milik satu Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Relasi: Satu Module memiliki banyak Lesson (materi)
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'module_id', 'module_id');
    }

    /**
     * Relasi: Satu Module bisa memiliki satu Quiz
     */
    public function quiz()
    {
        return $this->hasOne(Quiz::class, 'module_id', 'module_id');
    }
}