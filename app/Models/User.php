<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Tentukan Primary Key
    protected $primaryKey = 'user_id';

    // Tabel ini hanya punya 'created_at', tidak punya 'updated_at'
    public const UPDATED_AT = null;

    /**
     * Kolom yang boleh diisi (mass assignable)
     */
    protected $fillable = [
        'username',
        'full_name',
        'email',
        'password', // 'password' virtual, akan di-hash ke 'password_hash'
        'role',
        'level_id',
    ];

    /**
     * Kolom yang disembunyikan saat di-serialisasi (misal, jadi JSON)
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Mutator: Otomatis hash password saat di-set
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = Hash::make($value);
    }

    /**
     * Relasi: Siswa (student) belongsTo satu Level (Jenjang)
     */
    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }

    /**
     * Relasi: User (teacher) bisa membuat banyak Course
     */
    public function createdCourses()
    {
        return $this->hasMany(Course::class, 'created_by_user_id', 'user_id');
    }

    /**
     * Relasi: User (student) bisa mendaftar (enroll) banyak Course
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'user_id', 'user_id');
    }

    /**
     * Relasi: User (student) bisa memiliki banyak percobaan kuis
     */
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'user_id', 'user_id');
    }
}