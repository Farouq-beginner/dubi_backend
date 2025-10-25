<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'level_id';
    public $timestamps = false; // Tabel ini tidak punya timestamp

    /**
     * Relasi: Satu Level memiliki banyak Course
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'level_id', 'level_id');
    }

    /**
     * Relasi: Satu Level memiliki banyak User (siswa)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'level_id', 'level_id');
    }
}