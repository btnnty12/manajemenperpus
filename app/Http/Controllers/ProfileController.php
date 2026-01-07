<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:pengguna,email,' . $user->id,
            'kata_sandi' => 'nullable|string|min:6|confirmed', // optional
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update field dasar
        $user->nama = $request->nama;
        $user->email = $request->email;
        $user->phone = $request->phone;

        // Update password jika diisi
        if ($request->kata_sandi) {
            $user->kata_sandi = $request->kata_sandi; // otomatis di-hash oleh mutator
        }

        // Upload foto profil jika ada
        if ($request->hasFile('profile_photo')) {
            // Hapus foto lama jika ada
            $oldFoto = $user->getOriginal('foto');
            if ($oldFoto && Storage::disk('public')->exists($oldFoto)) {
                Storage::disk('public')->delete($oldFoto);
            }

            // Simpan foto baru di storage/app/public/profile_photos
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            
            // Pastikan path disimpan dengan benar
            $user->foto = $path;
        }

        $user->save();
        
        // Refresh user untuk memastikan data terbaru
        $user->refresh();

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }
}