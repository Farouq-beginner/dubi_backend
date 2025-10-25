<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Handle Registrasi User Baru
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', Rule::in(['student', 'teacher'])], // Admin tidak bisa daftar publik
            
            // 'level_id' wajib diisi jika rolenya 'student'
            'level_id' => 'required_if:role,student|nullable|integer|exists:levels,level_id',
        ]);

        // Buat user (mutator 'setPasswordAttribute' akan otomatis hash password)
        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Silakan login.'
        ], 201); // 201 = Created
    }

    /**
     * Handle Login User
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 1. Cari user berdasarkan email
        $user = User::where('email', $data['email'])->first();

        // 2. Cek user ada DAN password-nya cocok
        // Kita cek 'password' (input) dengan 'password_hash' (database)
        if (! $user || ! Hash::check($data['password'], $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 401); // 401 = Unauthorized
        }

        // 3. Buat API Token
        $token = $user->createToken('dubi-mobile-token')->plainTextToken;

        // 4. Kirim balasan (token + data user)
        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Handle Logout User
     */
    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}