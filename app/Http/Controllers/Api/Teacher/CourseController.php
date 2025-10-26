<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response; // Untuk respons 403 manual

class CourseController extends Controller
{
    /**
     * Menyimpan Course baru yang dibuat oleh Teacher.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. OTORISASI MANUAL: Cek Role Guru
        // Karena middleware 'can:is-teacher' gagal, kita cek manual.
        if ($user->role !== 'teacher') {
            return response()->json([
                'message' => 'Hanya Guru yang diizinkan untuk menambah Kursus.'
            ], Response::HTTP_FORBIDDEN); // Mengembalikan 403 Forbidden secara manual
        }

        // 2. VALIDASI
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level_id' => 'required|integer|exists:levels,level_id',
            'subject_id' => 'required|integer|exists:subjects,subject_id',
        ]);

        // 3. Gabungkan data & ID guru, lalu buat Course
        $course = Course::create($validatedData + [
            'created_by_user_id' => $user->user_id
        ]);

        // 4. Kembalikan data Course yang baru dibuat
        return response()->json([
            'success' => true,
            'message' => 'Kursus baru berhasil ditambahkan!',
            'data' => $course->fresh()
        ], Response::HTTP_CREATED); // 201 Created
    }

    public function update(Request $request, Course $course)
    {
        $user = Auth::user();

        // 1. Otorisasi Manual: Hanya Guru yang diizinkan
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        // 2. Otorisasi Manual: Cek Kepemilikan
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda bukan pemilik kursus ini.'], Response::HTTP_FORBIDDEN);
        }

        // 3. Validasi
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level_id' => 'required|integer|exists:levels,level_id',
            'subject_id' => 'required|integer|exists:subjects,subject_id',
        ]);

        // 4. Update dan Simpan
        $course->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Kursus berhasil diperbarui!',
            'data' => $course->fresh()
        ]);
    }

    /**
     * Menghapus Course.
     */
    public function destroy(Course $course)
    {
        $user = Auth::user();

        // 1. Otorisasi Manual: Hanya Guru
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        // 2. Otorisasi Manual: Cek Kepemilikan
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda bukan pemilik kursus ini.'], Response::HTTP_FORBIDDEN);
        }

        // 3. Hapus
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kursus berhasil dihapus.'
        ]);
    }
}