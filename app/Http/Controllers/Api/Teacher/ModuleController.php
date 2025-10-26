<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ModuleController extends Controller // Nama Class tetap ModuleController
{
    /**
     * Menyimpan Module baru yang dibuat oleh Teacher.
     */
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        // 1. OTORISASI MANUAL: Cek Role Guru
        if ($user->role !== 'teacher') {
            return response()->json([
                'message' => 'Hanya Guru yang diizinkan untuk menambah Modul.'
            ], Response::HTTP_FORBIDDEN); 
        }

        // 2. OTORISASI MANUAL: Cek Kepemilikan Kursus
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json([
                'message' => 'Anda tidak memiliki izin untuk mengedit kursus ini. (Bukan pemilik kursus)'
            ], Response::HTTP_FORBIDDEN); 
        }

        // 3. VALIDASI & BUAT DATA
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);
        
        // Jika lolos kedua check 403, lanjutkan
        $order = $course->modules()->max('order_index') + 1;
        
        $module = $course->modules()->create([
            'title' => $validated['title'],
            'order_index' => $order
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modul baru berhasil ditambahkan!',
            'data' => $module->load('lessons')
        ], Response::HTTP_CREATED); 
    }

    public function update(Request $request, Course $course, Module $module)
    {
        $user = Auth::user();

        // Cek apakah Modul ini milik Course tsb (Optional)
        if ($module->course_id !== $course->course_id) {
            return response()->json(['message' => 'Modul tidak ditemukan di kursus ini.'], Response::HTTP_NOT_FOUND);
        }
        
        // 1. Otorisasi Manual: Cek Role & Kepemilikan Course
        if ($user->role !== 'teacher' || $user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        // 2. Validasi
        $validated = $request->validate(['title' => 'required|string|max:255']);

        // 3. Update
        $module->update($validated);

        return response()->json(['success' => true, 'message' => 'Modul berhasil diperbarui!']);
    }

    /**
     * Menghapus Module.
     */
    public function destroy(Course $course, Module $module)
    {
        $user = Auth::user();

        // 1. Otorisasi Manual: Cek Role & Kepemilikan Course
        if ($user->role !== 'teacher' || $user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        // 2. Hapus
        $module->delete();

        return response()->json(['success' => true, 'message' => 'Modul berhasil dihapus!']);
    }
}