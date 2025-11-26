<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\SendVerificationCode;

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

// --- [TAMBAHKAN DI SINI UNTUK SINGLE DEVICE] ---
        // Hapus semua token lama agar login di perangkat lain terkeluar
        $user->tokens()->delete(); 
        // -----------------------------------------------
        
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

/**
     * [BARU] 1. Lupa Password - Kirim Kode ke Email (PUBLIK)
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $email = $request->email;
        $code = rand(100000, 999999);

        try {
            Log::info("Mencoba mengirim email ke: " . $email);
            // Simpan kode
            DB::table('password_reset_codes')->updateOrInsert(
                ['email' => $email],
                ['code' => $code, 'created_at' => now()]
            );

            // Kirim email
            Mail::to($email)->send(new SendVerificationCode($code));

            Log::info("Email terkirim ke: " . $email);

            return response()->json(['message' => 'Kode verifikasi terkirim ke email Anda.']);

        } catch (\Exception $e) {
            Log::error("Gagal mengirim email ke $email: " . $e->getMessage());

            return response()->json(['message' => 'Gagal mengirim email.'], 500);
        }
    }

    /**
     * [BARU] 2. Reset Password dengan Kode (PUBLIK)
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|min:6|max:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Cek kode
        $record = DB::table('password_reset_codes')
                    ->where('email', $validated['email'])
                    ->where('code', $validated['code'])
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 422);
        }

        // Cek kadaluarsa (10 menit)
        if (now()->diffInMinutes($record->created_at) > 10) {
            return response()->json(['message' => 'Kode kadaluarsa.'], 422);
        }

        // Update User
        $user = User::where('email', $validated['email'])->first();
        $user->password_hash = Hash::make($validated['password']);
        $user->save();

        // Hapus kode
        DB::table('password_reset_codes')->where('email', $validated['email'])->delete();

        return response()->json(['message' => 'Password berhasil diubah. Silakan login.']);
    }

}