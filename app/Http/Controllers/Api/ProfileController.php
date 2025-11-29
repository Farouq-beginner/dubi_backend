<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// WAJIB ditambahkan
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationCode;


class ProfileController extends Controller
{
    /**
     * Handle Upload/Ganti Foto Profil
     */
    public function updatePhoto(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            // 'photo' wajib ada, harus file gambar (jpeg,png,jpg,gif,svg), maks 2MB (2048 KB)
            'photo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        // Ambil user yang sedang login
        $user = auth()->user(); 

        if ($request->hasFile('photo')) {
            // 2. Hapus Foto Lama (Jika ada)
            // Kita cek apakah dia punya foto lama DAN file nya beneran ada di storage
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // 3. Simpan Foto Baru
            // File akan disimpan di folder 'storage/app/public/profile-photos'
            // Laravel otomatis generate nama file unik.
            $path = $request->file('photo')->store('profile-photos', 'public');

            // 4. Update Database
            // Simpan path relatifnya saja (contoh: 'profile-photos/namafileacak.jpg')
            $user->profile_photo_path = $path;
            $user->save();

            // 5. Kembalikan Respon Sukses & URL Lengkap
            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui.',
                'data' => [
                    // Kita kirim URL lengkap agar Flutter bisa langsung pakai
                    'url' => asset('storage/' . $path),
                    'path' => $path
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal mengupload file.'], 400);
    }

/**
     * 2. API Kirim Kode Verifikasi Ganti Password
     */
    public function sendPasswordCode(Request $request)
    {
        $user = Auth::user();
        $code = rand(100000, 999999);

        try {
            // 1. Simpan kode ke DB
            DB::table('password_reset_codes')->updateOrInsert(
                ['email' => $user->email],
                ['code' => $code, 'created_at' => now()]
            );

            // 2. Kirim email
            // (Jika ini lambat, nanti bisa pakai Queue, tapi sekarang kita pakai langsung)
            Mail::to($user->email)->send(new SendVerificationCode($code));

            // 3. Return Sukses yang Jelas
            return response()->json([
                'success' => true,
                'message' => 'Kode verifikasi terkirim!'
            ], 200);

        } catch (\Exception $e) {
            // Log error untuk debugging
            \Illuminate\Support\Facades\Log::error("Mail Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email (Server Error).'
            ], 500);
        }
    }

/**
     * 3. API Reset Password dengan Kode
     */
    public function resetPasswordWithCode(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'code' => 'required|string|min:6|max:6',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' akan cek 'password_confirmation'
        ]);

        // Cek kodenya
        $record = \DB::table('password_reset_codes')
            ->where('email', $user->email)
            ->where('code', $validated['code'])
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 422);
        }

        // Cek jika kode kadaluarsa (misal: 10 menit)
        if (now()->diffInMinutes($record->created_at) > 10) {
            \DB::table('password_reset_codes')->where('email', $user->email)->delete();
            return response()->json(['message' => 'Kode kadaluarsa. Silakan minta kode baru.'], 422);
        }

        // Update password
        $user->password = bcrypt($validated['password']);
        $user->save();

        // Hapus kode yang sudah dipakai
        \DB::table('password_reset_codes')->where('email', $user->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset.'
        ], 200);
    }

    /**
    * Handle Update Data Profil (Nama & Email)
    * (Ini untuk fitur Edit Profil yang textfield)
    */
/**
     * Handle Update Data Profil (Nama & Email)
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user(); // Ambil user yang sedang login

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            // Email harus unik, kecuali milik user ini sendiri
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('users')->ignore($user->user_id, 'user_id')],
            // Level ID opsional, hanya jika user adalah student
            'level_id' => 'nullable|integer|exists:levels,level_id',
        ]);

        // Update data
        $user->full_name = $validated['full_name'];
        $user->email = $validated['email'];
        
        // Jika student, update level_id juga
        if ($user->role === 'student' && isset($validated['level_id'])) {
            $user->level_id = $validated['level_id'];
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user
        ]);
    }
}