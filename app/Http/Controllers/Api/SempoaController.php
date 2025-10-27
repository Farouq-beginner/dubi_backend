<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SempoaProgress; // Kita akan buat model ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SempoaController extends Controller
{
    /**
     * Mengambil progres Sempoa saat ini (level tertinggi & skor).
     */
    public function getProgress()
    {
        $user = Auth::user();
        
        // Ambil data progres atau buat yang baru
        $progress = SempoaProgress::firstOrCreate(
            ['user_id' => $user->user_id],
            ['highest_level' => 1, 'high_score' => 0, 'highest_streak' => 0]
        );

        return response()->json(['success' => true, 'data' => $progress]);
    }

    /**
     * Menyimpan skor tinggi baru dan level.
     */
    public function saveProgress(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'new_score' => 'required|integer|min:0',
            'new_level' => 'required|integer|min:1',
            'new_streak' => 'nullable|integer|min:0',
        ]);

        $progress = SempoaProgress::firstOrCreate(['user_id' => $user->user_id]);

        // Logika Update: Hanya update jika skor/level lebih tinggi
        if ($validated['new_score'] > $progress->high_score) {
            $progress->high_score = $validated['new_score'];
        }
        if ($validated['new_level'] > $progress->highest_level) {
            $progress->highest_level = $validated['new_level'];
        }
        if ($validated['new_streak'] > $progress->highest_streak) {
            $progress->highest_streak = $validated['new_streak'];
        }
        $progress->last_played_at = now();
        $progress->save();

        return response()->json(['success' => true, 'message' => 'Progress Sempoa tersimpan!']);
    }
}