<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /**
     * Daftarkan akun karyawan baru berdasarkan data registrasi dan data employee registry.
     *
     * @param array $data
     * @return User
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function register(array $data): User
    {
        try {
            return DB::transaction(function () use ($data) {
                // Kunci baris registry untuk mencegah race condition (pendaftaran bersamaan)
                $employee = DB::table('employee_registry')
                    ->where('nik', $data['nik'])
                    ->lockForUpdate()
                    ->first();

                // Validasi keberadaan NIK dan status aktif registry 
                if (! $employee || ! $employee->is_active) {
                    throw ValidationException::withMessages([
                        'nik' => 'NIK tidak terdaftar pada daftar karyawan. Hubungi Admin/HSE.',
                    ]);
                }

                // Cek apakah NIK sudah digunakan (termasuk user soft-deleted withTrashed)
                $nikUsed = User::withTrashed()
                    ->where('employee_registry_id', $employee->id)
                    ->exists();

                if ($nikUsed) {
                    throw ValidationException::withMessages([
                        'nik' => 'NIK sudah digunakan. Silakan masuk ke akun Anda.',
                    ]);
                }

                // Simpan akun baru (username diperbolehkan sama, NIK dan email unik)
                $user = new User();
                $user->username = $data['username'];
                $user->email = $data['email'];
                $user->password = $data['password']; // Model User memiliki cast 'password' => 'hashed'
                $user->name = $employee->full_name; // DIAMBIL dari employee_registry
                $user->department_id = $employee->department_id; // DIAMBIL dari employee_registry
                $user->employee_registry_id = $employee->id;
                $user->role = 'employee'; // Hardcode role employee
                $user->is_active = true;
                $user->save();

                return $user;
            }, 3);
        } catch (QueryException $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'users_email_unique') || str_contains($message, 'users.email')) {
                throw ValidationException::withMessages([
                    'email' => 'Email sudah digunakan.',
                ]);
            }
            if (str_contains($message, 'users_employee_registry_id_unique') || str_contains($message, 'employee_registry_id')) {
                throw ValidationException::withMessages([
                    'nik' => 'NIK sudah digunakan. Silakan masuk ke akun Anda.',
                ]);
            }

            throw $e;
        }
    }
}