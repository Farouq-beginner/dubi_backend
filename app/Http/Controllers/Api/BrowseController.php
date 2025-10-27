<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    /**
     * Mengambil semua level dan kursus yang terkait
     * (termasuk relasi subject di dalam course)
     */
    public function getAllCoursesByLevel()
    {
        // 'courses.subject' akan Eager Load relasi 'subject' di dalam 'courses'
        $levelsWithCourses = Level::with('courses.subject')
                                ->orderBy('level_id', 'asc') // Urutkan TK, SD, ... Umum
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $levelsWithCourses
        ]);
    }
}