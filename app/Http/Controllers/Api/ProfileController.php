<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // PENTING Untuk handle file
use Illuminate\Validation\Rule;

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
     * Handle Update Data Profil (Nama & Email)
     * (Ini untuk fitur Edit Profil yang textfield)
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'full_name' => 'required|string|max:100',
            // Email harus unik, TAPI abaikan (ignore) ID user yang sedang login saat ini
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->user_id, 'user_id')],
        ]);

        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user // Kembalikan data user terbaru
        ]);
    }
}