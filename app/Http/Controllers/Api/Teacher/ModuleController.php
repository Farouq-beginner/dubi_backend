<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function store(Request $request, Course $course)
    {
        // 1. OTORISASI: Cek apakah user ini boleh 'update' course ini.
        $this->authorize('update', $course);

        // 2. VALIDASI
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // 3. BUAT DATA
        // Kita hitung 'order_index' berikutnya
        $order = $course->modules()->max('order_index') + 1;

        $module = $course->modules()->create([
            'title' => $validated['title'],
            'order_index' => $order
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modul baru ditambahkan!',
            'data' => $module
        ], 201);
    }
}