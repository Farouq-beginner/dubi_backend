<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Import Auth

class CourseController extends Controller
{
    /**
     * Menyimpan Course baru yang dibuat oleh Teacher.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level_id' => 'required|integer|exists:levels,level_id',
            'subject_id' => 'required|integer|exists:subjects,subject_id',
        ]);

        // 2. Dapatkan ID guru yang sedang login
        $teacherUserId = Auth::id(); // atau $request->user()->user_id;

        // 3. Gabungkan data & ID guru, lalu buat Course
        $course = Course::create($validatedData + [
            'created_by_user_id' => $teacherUserId
        ]);

        // 4. Kembalikan data Course yang baru dibuat (tanpa relasi)
        // Kita panggil 'fresh()' untuk mengambil data murni dari DB
        return response()->json([
            'success' => true,
            'message' => 'Kursus baru berhasil ditambahkan!',
            'data' => $course->fresh() 
        ], 201); // 201 = Created
    }

    // TODO: Nanti kita tambahkan fungsi update() dan destroy() di sini
}