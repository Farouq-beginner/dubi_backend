<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    private function checkAdmin() {
        if (Auth::user()->role !== 'admin') {
            abort(response()->json(['message' => 'Hanya Admin.'], Response::HTTP_FORBIDDEN));
        }
    }

    public function update(Request $request, Course $course)
    {
        $this->checkAdmin();
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level_id' => 'required|integer|exists:levels,level_id',
            'subject_id' => 'required|integer|exists:subjects,subject_id',
        ]);
        
        try {
            $course->update($validatedData);
            return response()->json(['success' => true, 'message' => 'Kursus diperbarui oleh Admin.']);
        } catch (\Exception $e) { /* ... Error Handling ... */ }
    }

    public function destroy(Course $course)
    {
        $this->checkAdmin();
        try {
            $course->delete();
            return response()->json(['success' => true, 'message' => 'Kursus dihapus oleh Admin.']);
        } catch (\Exception $e) { /* ... Error Handling ... */ }
    }
}