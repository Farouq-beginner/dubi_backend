<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationCode;
use Illuminate\Support\Facades\Storage; // <-- Penting untuk Hapus Foto Lama
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    /**
     * 1. API Upload Foto Profil
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Maks 2MB
        ]);

        $user = Auth::user();

        // Hapus foto lama jika ada
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Simpan foto baru
        $path = $request->file('photo')->store('profile-photos', 'public');

        // Update path di database
        $user->profile_photo_path = $path;
        $user->save();

        // Kembalikan URL lengkap ke foto
        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diunggah!',
            'data' => [
                'url' => Storage::disk('public')->url($path)
            ]
        ]);
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
        $user = Auth::user();

        $validated = $request->validate([
            'code' => 'required|string|min:6|max:6',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' akan cek 'password_confirmation'
        ]);

        // Cek kodenya
        $record = DB::table('password_reset_codes')
            ->where('email', $user->email)
            ->where('code', $validated['code'])
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Kode verifikasi salah.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Cek jika kode kadaluarsa (misal: 10 menit)
        if (now()->diffInMinutes($record->created_at) > 10) {
            DB::table('password_reset_codes')->where('email', $user->email)->delete();
            return response()->json(['message' => 'Kode kadaluarsa. Silakan minta kode baru.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Update password user
        $user->password_hash = Hash::make($validated['password']);
        $user->save();

        // Hapus kode
        DB::table('password_reset_codes')->where('email', $user->email)->delete();

        return response()->json(['message' => 'Password Anda telah berhasil diubah!']);
    }
}