<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SempoaProgress extends Model
{
    protected $table = 'sempoa_progress';
    protected $primaryKey = 'progress_id';
    public $timestamps = false;
    
    // Pastikan ini match dengan tabel baru
    protected $fillable = ['user_id', 'highest_level', 'high_score', 'highest_streak', 'last_played_at'];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}