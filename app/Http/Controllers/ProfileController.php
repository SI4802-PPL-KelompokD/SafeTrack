<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna yang sedang login (PBI-08).
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['department', 'employeeRegistry']);

        return view('profile.edit', compact('user'));
    }

    /**
     * Perbarui data profil akun pengguna yang sedang login (PBI-08).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Aturan validasi dasar (nama, email, foto profil)
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'profile_photo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048', // 2 MB
            ],
        ];

        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan oleh pengguna lain.',
            'profile_photo.file' => 'Foto profil harus berupa file yang valid.',
            'profile_photo.image' => 'Foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Format foto profil harus JPG, JPEG, PNG, atau WebP.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2 MB.',
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
        ];

        // Jika salah satu kolom password diisi, wajib validasi ketiga kolom
        $isChangingPassword = $request->filled('current_password')
            || $request->filled('password')
            || $request->filled('password_confirmation');

        if ($isChangingPassword) {
            $rules['current_password'] = ['required', 'string'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required', 'string'];
        }

        $validated = $request->validate($rules, $messages);

        // Jika mengganti password, verifikasi password saat ini
        if ($isChangingPassword) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Password saat ini tidak sesuai.'],
                ]);
            }

            $user->password = Hash::make($request->input('password'));
        }

        // Penanganan opsi hapus foto profil
        if ($request->boolean('remove_photo')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        }

        // Penanganan unggah foto profil baru
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $photoPath;
        }

        // Simpan pembaruan nama dan email
        $user->name = trim($validated['name']);
        $user->email = trim($validated['email']);
        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
