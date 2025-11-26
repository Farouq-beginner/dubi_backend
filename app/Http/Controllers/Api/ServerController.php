<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    // 1. Cek Kesehatan Server (Public)
    public function checkStatus()
    {
        return response()->json([
            'status' => 'online',
            'message' => 'Server is running normally',
            'timestamp' => now()
        ]);
    }

    // 2. Cek Status Token/User (Protected)
    public function checkSession(Request $request)
    {
        $user = Auth::user();
        // Di sini kita bisa tambah logika "Force Logout" jika mau
        // Untuk sekarang, cukup kembalikan data user valid
        return response()->json([
            'valid' => true,
            'user_id' => $user->user_id,
            'role' => $user->role
        ]);
    }
    
    // 3. Logout dari device lain (Menghapus semua token KECUALI yang sekarang)
    public function logoutOtherDevices(Request $request)
    {
        $user = Auth::user();
        
        // Hapus semua token
        $user->tokens()->delete();
        
        // Buat token baru untuk device ini
        $newToken = $user->createToken('dubi-mobile-token')->plainTextToken;
        
        return response()->json([
            'message' => 'Logged out from other devices.',
            'token' => $newToken
        ]);
    }
}