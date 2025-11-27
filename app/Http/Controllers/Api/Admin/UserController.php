<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Helper untuk cek Admin
     */
    private function checkAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(response()->json(['message' => 'Hanya Admin yang diizinkan.'], Response::HTTP_FORBIDDEN));
        }
    }

    /**
     * Fitur 1: Melihat Semua Pengguna
     */
    public function index()
    {
        $this->checkAdmin(); // <-- Cek Manual
        
        $adminId = Auth::id();
        $users = User::where('user_id', '!=', $adminId)
                    ->with('level')
                    ->orderBy('role')
                    ->orderBy('full_name')
                    ->get();
                    
        return response()->json(['success' => true, 'data' => $users]);
    }

    /**
     * Fitur 2 & 4: Mengubah Data Pengguna
     */
    public function update(Request $request, User $user)
    {
        $this->checkAdmin(); // <-- Cek Manual
        
        if ($user->user_id === Auth::id()) {
            return response()->json(['message' => 'Tidak dapat mengedit data admin sendiri.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'role' => ['required', Rule::in(['student', 'teacher'])],
            'level_id' => 'nullable|integer|exists:levels,level_id',
            'password' => 'nullable|string|min:8'
        ]);

        try {
            if (!empty($validated['password'])) {
                $user->password_hash = Hash::make($validated['password']);
            }
            $user->full_name = $validated['full_name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'];
            $user->level_id = $validated['role'] === 'student' ? $validated['level_id'] : null;
            $user->save();

            return response()->json([
                'success' => true, 
                'message' => 'Pengguna berhasil diperbarui!', 
                'data' => $user->load('level')
            ]);
        } catch (\Exception $e) {
            Log::error("ADMIN UPDATE USER FAILED: " . $e->getMessage());
            return response()->json(['message' => 'Update gagal, terjadi kesalahan server.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Fitur 3: Menghapus Pengguna
     */
    public function destroy(User $user)
    {
        $this->checkAdmin(); // <-- Cek Manual
        
        if ($user->user_id === Auth::id()) {
            return response()->json(['message' => 'Tidak dapat menghapus akun admin sendiri.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $user->delete(); 
            return response()->json(['success' => true, 'message' => 'Pengguna berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error("ADMIN DELETE USER FAILED: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: Pengguna ini mungkin masih memiliki data terkait.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}