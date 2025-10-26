<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
Use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    public function store(Request $request, Module $module)
    {
        $user = Auth::user();
        // Pastikan relasi course dimuat di model Module jika tidak akan error
        $course = $module->course;

        // 1. OTORISASI MANUAL: Cek Role Guru
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Hanya Guru yang diizinkan untuk menambah Materi.'], Response::HTTP_FORBIDDEN);
        }

        // 2. OTORISASI MANUAL: Cek Kepemilikan Kursus
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda tidak memiliki izin untuk menambah materi di kursus ini.'], Response::HTTP_FORBIDDEN);
        }

        // 3. VALIDASI & BUAT DATA
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:video,text,pdf',
            'content_body' => 'nullable|string',
        ]);

        $order = $module->lessons()->max('order_index') + 1;

        $lesson = $module->lessons()->create([
            'title' => $validated['title'],
            'content_type' => $validated['content_type'],
            'content_body' => $validated['content_body'] ?? null,
            'order_index' => $order
        ]);
        
        $module->load('lessons');

        return response()->json([
            'success' => true,
            'message' => 'Materi baru berhasil ditambahkan!',
            'data' => $module
        ], Response::HTTP_CREATED);
    }

    // --- [BARU] FUNGSI UPDATE UNTUK EDIT MATERI ---
    public function update(Request $request, Module $module, Lesson $lesson)
    {
        $user = Auth::user();
        $course = $module->course;

        // 1. OTORISASI MANUAL: Cek Role Guru
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Hanya Guru yang diizinkan.'], Response::HTTP_FORBIDDEN);
        }

        // 2. OTORISASI MANUAL: Cek Kepemilikan Course
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda bukan pemilik kursus ini.'], Response::HTTP_FORBIDDEN);
        }

        // 3. Cek Asosiasi: Pastikan Lesson ini milik Module yang benar
        if ($lesson->module_id !== $module->module_id) {
             return response()->json(['message' => 'Materi tidak ditemukan di Modul ini.'], Response::HTTP_NOT_FOUND);
        }

        // 4. VALIDASI
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => ['required', Rule::in(['video', 'text', 'pdf'])],
            'content_body' => 'nullable|string',
        ]);

        // 5. UPDATE DATA
        try {
            $lesson->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil diperbarui!'
            ], Response::HTTP_OK); // 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui materi: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Module $module, Lesson $lesson)
    {
        $user = Auth::user();
        $course = $module->course;

        // 1. Otorisasi Manual: Cek Role Guru
        if ($user->role !== 'teacher') {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        // 2. Otorisasi Manual: Cek Kepemilikan Course
        if ($user->user_id !== $course->created_by_user_id) {
             return response()->json(['message' => 'Anda bukan pemilik kursus ini.'], Response::HTTP_FORBIDDEN);
        }

        // 3. Cek Asosiasi: Pastikan Lesson ini milik Module yang benar
        if ($lesson->module_id !== $module->module_id) {
             return response()->json(['message' => 'Materi tidak ditemukan di Modul ini.'], Response::HTTP_NOT_FOUND);
        }

        // 4. Hapus
        $lesson->delete();

        return response()->json(['success' => true, 'message' => 'Materi berhasil dihapus!']);
    }
}