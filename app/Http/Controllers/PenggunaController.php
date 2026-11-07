<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeletePenggunaRequest;
use App\Http\Requests\UpdatePenggunaRequest;
use App\Models\Department;
use App\Models\EmploymentStatus;
use App\Models\User;
use App\Services\PenggunaService;
use Illuminate\Http\JsonResponse;
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
    public function update(UpdatePenggunaRequest $request, User $pengguna): JsonResponse|RedirectResponse
    {
        $this->authorizeAdmin();

        if ($pengguna->role !== 'employee') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hanya akun karyawan yang dapat dikelola. Akun Admin bukan merupakan target pengelolaan.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Hanya akun karyawan yang dapat dikelola.');
        }

        try {
            /** @var User $actor */
            $actor = Auth::user();
            $updatedUser = $this->penggunaService->update($pengguna, $request->validated(), $actor);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data akun karyawan berhasil diperbarui.',
                    'user' => [
                        'id' => $updatedUser->id,
                        'name' => $updatedUser->name,
                        'email' => $updatedUser->email,
                        'nik' => $updatedUser->employeeRegistry?->nik ?? '-',
                        'department_id' => $updatedUser->department_id,
                        'department_name' => $updatedUser->department?->name ?? '-',
                        'employment_status_id' => $updatedUser->employeeRegistry?->employment_status_id ?? null,
                        'employment_status_name' => $updatedUser->employeeRegistry?->employmentStatus?->name ?? 'Tetap',
                        'is_active' => (bool) $updatedUser->is_active,
                        'role' => $updatedUser->role,
                    ],
                    'stats' => [
                        'totalUsers' => User::count(),
                        'activeUsers' => User::where('is_active', true)->count(),
                        'totalDepartments' => Department::where('is_active', true)->count(),
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Data akun karyawan berhasil diperbarui.');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan sistem saat memperbarui data: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat memperbarui data: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Soft delete akun karyawan dengan mencatat alasan penghapusan (FR-07 / UC-33).
     */
    public function destroy(DeletePenggunaRequest $request, User $pengguna): JsonResponse|RedirectResponse
    {
        $this->authorizeAdmin();

        if ($pengguna->role !== 'employee') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hanya akun karyawan yang dapat dihapus. Akun Admin tidak dapat dihapus di sini.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Hanya akun karyawan yang dapat dihapus.');
        }

        try {
            /** @var User $actor */
            $actor = Auth::user();
            $this->penggunaService->delete($pengguna, $request->validated('deletion_reason'), $actor);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Akun karyawan berhasil dihapus.',
                    'deleted_id' => $pengguna->id,
                    'stats' => [
                        'totalUsers' => User::count(),
                        'activeUsers' => User::where('is_active', true)->count(),
                        'totalDepartments' => Department::where('is_active', true)->count(),
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Akun karyawan berhasil dihapus.');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan sistem saat menghapus akun: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat menghapus akun: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Helper otorisasi internal untuk peran admin / super_admin.
     */
    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->can('access-admin'), 403, 'Akses ditolak.');
    }
}
