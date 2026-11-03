<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PenggunaService
{
    /**
     * Memperbarui departemen, status kepegawaian, dan status aktif akun karyawan (FR-06 / UC-33).
     *
     * @param  array{department_id: int, employment_status_id: int, is_active: bool|int|string}  $data
     *
     * @throws ValidationException
     */
    public function update(User $user, array $data, User $actor): User
    {
        $isActive = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);

        // Aturan keamanan: Admin tidak boleh menonaktifkan akunnya sendiri
        if ($actor->id === $user->id && ! $isActive) {
            throw ValidationException::withMessages([
                'is_active' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ]);
        }

        return DB::transaction(function () use ($user, $data, $isActive) {
            // Perbarui data pada tabel users
            $user->department_id = $data['department_id'];
            $user->is_active = $isActive;
            $user->save();

            // Sinkronkan data ke tabel employee_registry jika terkait
            if ($user->employeeRegistry) {
                $registry = $user->employeeRegistry;
                $registry->department_id = $data['department_id'];
                $registry->employment_status_id = $data['employment_status_id'];
                $registry->is_active = $isActive;
                $registry->save();
            }

            // Jika akun dinonaktifkan, akhiri sesi aktif yang tersimpan
            if (! $isActive) {
                $this->revokeUserSessions($user);
            }

            return $user->fresh(['department', 'employeeRegistry.employmentStatus']);
        });
    }

    /**
     * Melakukan soft delete akun pengguna dengan mencatat waktu, pelaku, dan alasan (FR-07 / UC-33).
     *
     * @throws ValidationException
     */
    public function delete(User $user, string $reason, User $actor): bool
    {
        $trimmedReason = trim($reason);
        if ($trimmedReason === '') {
            throw ValidationException::withMessages([
                'deletion_reason' => 'Alasan penghapusan akun wajib diisi.',
            ]);
        }

        // Aturan keamanan: Admin tidak boleh menghapus akunnya sendiri
        if ($actor->id === $user->id) {
            throw ValidationException::withMessages([
                'deletion_reason' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ]);
        }

        return DB::transaction(function () use ($user, $trimmedReason, $actor) {
            // Simpan informasi alasan dan admin yang melakukan penghapusan
            $user->deleted_by = $actor->id;
            $user->deletion_reason = $trimmedReason;
            $user->is_active = false;
            $user->save();

            // Jalankan SoftDeletes Eloquent
            $deleted = (bool) $user->delete();

            // Putuskan sesi yang sedang berjalan
            $this->revokeUserSessions($user);

            return $deleted;
        });
    }

    /**
     * Menghapus sesi aktif pengguna dari database dan menghapus remember token.
     */
    protected function revokeUserSessions(User $user): void
    {
        $user->forceFill(['remember_token' => null])->saveQuietly();

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }
}
