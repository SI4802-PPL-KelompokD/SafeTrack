<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeletePenggunaRequest;
use App\Http\Requests\UpdatePenggunaRequest;
use App\Models\Department;
use App\Models\EmploymentStatus;
use App\Models\User;
use App\Services\PenggunaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function __construct(
        protected PenggunaService $penggunaService
    ) {}

    /**
     * Tampilkan daftar karyawan dengan pencarian, filter, dan pagination (FR-06 / UC-33).
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $search = $request->query('search');
        $departmentId = $request->query('department_id');
        $status = $request->query('status');

        $users = User::query()
            ->with(['department', 'employeeRegistry.employmentStatus'])
            ->search($search)
            ->filterDepartemen($departmentId)
            ->filterStatus($status)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalDepartments = Department::where('is_active', true)->count();

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $employmentStatuses = EmploymentStatus::orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('admin.pengguna.index', [
            'users' => $users,
            'departments' => $departments,
            'employmentStatuses' => $employmentStatuses,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'totalDepartments' => $totalDepartments,
            'filters' => [
                'search' => $search,
                'department_id' => $departmentId,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Perbarui departemen, status kepegawaian, dan status akun karyawan (FR-06 / UC-33).
     */
    public function update(UpdatePenggunaRequest $request, User $pengguna): RedirectResponse
    {
        $this->authorizeAdmin();

        try {
            /** @var User $actor */
            $actor = Auth::user();
            $this->penggunaService->update($pengguna, $request->validated(), $actor);

            return redirect()->back()->with('success', 'Data akun karyawan berhasil diperbarui.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat memperbarui data: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Soft delete akun karyawan dengan mencatat alasan penghapusan (FR-07 / UC-33).
     */
    public function destroy(DeletePenggunaRequest $request, User $pengguna): RedirectResponse
    {
        $this->authorizeAdmin();

        try {
            /** @var User $actor */
            $actor = Auth::user();
            $this->penggunaService->delete($pengguna, $request->validated('deletion_reason'), $actor);

            return redirect()->back()->with('success', 'Akun karyawan berhasil dihapus.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat menghapus akun: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Simulasi reset password oleh admin (FR-06 / FR-08 Stub).
     *
     * TODO(PBI-08): integrasikan dengan pembuatan token PasswordResetToken setelah PBI-08 di-merge.
     */
    public function resetPassword(User $pengguna): RedirectResponse
    {
        $this->authorizeAdmin();

        return redirect()->back()->with(
            'info',
            'Fitur simulasi reset password akan tersedia penuh setelah modul PBI-08 selesai diintegrasikan. Admin tidak menetapkan password secara langsung.'
        );
    }

    /**
     * Helper otorisasi internal untuk peran admin / super_admin.
     *
     * // TODO(PBI-07): ganti dengan middleware role setelah PBI-07 di-merge.
     */
    private function authorizeAdmin(): void
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, ['super_admin', 'admin'], true)) {
            abort(403, 'Akses ditolak. Fitur ini hanya dapat diakses oleh Admin/HSE.');
        }
    }
}
