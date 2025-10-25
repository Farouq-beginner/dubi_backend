<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function store(Request $request, Module $module)
    {
        // 1. OTORISASI: Cek izin melalui course milik module ini
        $this->authorize('update', $module->course);

        // 2. VALIDASI
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:video,text,pdf',
            'content_body' => 'nullable|string',
        ]);

        // 3. BUAT DATA
        $order = $module->lessons()->max('order_index') + 1;

        $lesson = $module->lessons()->create([
            'title' => $validated['title'],
            'content_type' => $validated['content_type'],
            'content_body' => $validated['content_body'] ?? null,
            'order_index' => $order
        ]);

        // Muat relasi 'lessons' di modul agar bisa di-refresh di UI
        $module->load('lessons');

        return response()->json([
            'success' => true,
            'message' => 'Materi baru ditambahkan!',
            'data' => $module // Kirim balik modul yg sudah di-update
        ], 201);
    }
}