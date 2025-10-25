<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'subject_id';
    public $timestamps = false; // Tabel ini tidak punya timestamp

    /**
     * Relasi: Satu Subject memiliki banyak Course
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'subject_id', 'subject_id');
    }
}