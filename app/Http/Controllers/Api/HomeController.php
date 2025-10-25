<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Mengambil kursus yang relevan untuk user yang sedang login.
     * Siswa -> Kursus sesuai level.
     * Guru -> Kursus yang dia buat.
     */
    public function myCourses(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'student') {
            // Siswa: Ambil kursus berdasarkan level_id siswa
            $courses = Course::where('level_id', $user->level_id)->get();
        } elseif ($user->role === 'teacher') {
            // Guru: Ambil kursus yang dia buat
            $courses = Course::where('created_by_user_id', $user->user_id)->get();
        } else {
            // Admin atau role lain: Ambil semua kursus
            $courses = Course::all();
        }

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }
}