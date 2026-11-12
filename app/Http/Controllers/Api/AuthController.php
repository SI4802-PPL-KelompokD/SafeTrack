<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Mobile API Login (POST /api/login)
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $validated['login'];
        $password = $validated['password'];

        // Cari berdasarkan username atau email
        $user = User::with(['department', 'employeeRegistry'])
            ->where(function ($query) use ($loginInput) {
                $query->where('username', $loginInput)
                      ->orWhere('email', $loginInput);
            })
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username/email atau password salah.',
            ], 401);
        }

        // Periksa apakah akun aktif
        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda berstatus nonaktif. Silakan hubungi administrator.',
            ], 403);
        }

        // Buat token autentikasi Sanctum untuk mobile
        $token = $user->createToken('safetrack-mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'department' => $user->department ? [
                        'id' => $user->department->id,
                        'name' => $user->department->name,
                        'is_hse' => (bool) $user->department->is_hse,
                    ] : null,
                    'employee_registry' => $user->employeeRegistry ? [
                        'nik' => $user->employeeRegistry->nik,
                        'full_name' => $user->employeeRegistry->full_name,
                    ] : null,
                ],
            ],
        ], 200);
    }

    /**
     * Mobile API User Profile (GET /api/me)
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['department', 'employeeRegistry']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department ? [
                    'id' => $user->department->id,
                    'name' => $user->department->name,
                    'is_hse' => (bool) $user->department->is_hse,
                ] : null,
                'employee_registry' => $user->employeeRegistry ? [
                    'nik' => $user->employeeRegistry->nik,
                    'full_name' => $user->employeeRegistry->full_name,
                ] : null,
            ],
        ], 200);
    }

    /**
     * Mobile API Logout (POST /api/logout)
     */
    public function logout(Request $request)
    {
        // Hapus token akses yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil. Token telah dicabut.',
        ], 200);
    }
}
