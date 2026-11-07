<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\EmploymentStatus;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggunaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $employeeUser;

    protected Department $hseDepartment;

    protected Department $opsDepartment;

    protected EmploymentStatus $permanentStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);

        $this->adminUser = User::where('username', 'admin')->firstOrFail();
        $this->employeeUser = User::where('username', 'karyawan')->firstOrFail();

        $this->hseDepartment = Department::where('code', 'HSE')->firstOrFail();
        $this->opsDepartment = Department::where('code', 'OPS')->firstOrFail();
        $this->permanentStatus = EmploymentStatus::where('code', 'permanent')->firstOrFail();
    }

    /**
     * Admin dapat melihat halaman daftar kelola karyawan beserta kartu ringkasan.
     */
    public function test_admin_can_view_pengguna_list(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola karyawan');
        $response->assertSee('ADMINISTRASI');
        $response->assertSee('Seluruh pengguna');
        $response->assertSee('Akun aktif');
        $response->assertSee('Departemen');
        $response->assertSee('Karyawan Demo');
        $response->assertSee('DEMO003');
    }

    /**
     * Karyawan biasa tidak boleh mengakses halaman kelola karyawan (403 Forbidden).
     */
    public function test_employee_cannot_access_pengguna_management(): void
    {
        $response = $this->actingAs($this->employeeUser)
            ->get(route('admin.pengguna.index'));

        $response->assertForbidden()->assertViewIs('errors.403');
    }

    /**
     * Pengguna tanpa autentikasi dialihkan ke login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.pengguna.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Pencarian satu kolom berdasarkan nama, NIK, dan email berfungsi.
     */
    public function test_admin_can_search_pengguna_by_name_nik_and_email(): void
    {
        // 1. Cari berdasarkan nama
        $resName = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index', ['search' => 'Karyawan Demo']));
        $resName->assertStatus(200);
        $resName->assertSee('Karyawan Demo');
        $resName->assertDontSee('Petugas HSE');

        // 2. Cari berdasarkan NIK
        $resNik = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index', ['search' => 'DEMO002']));
        $resNik->assertStatus(200);
        $resNik->assertSee('Petugas HSE');
        $resNik->assertDontSee('Karyawan Demo');

        // 3. Cari berdasarkan email
        $resEmail = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index', ['search' => 'hse@safetrack.test']));
        $resEmail->assertStatus(200);
        $resEmail->assertSee('Petugas HSE');
        $resEmail->assertDontSee('Karyawan Demo');
    }

    /**
     * Filter berdasarkan departemen berfungsi.
     */
    public function test_admin_can_filter_pengguna_by_department(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index', ['department_id' => $this->opsDepartment->id]));

        $response->assertStatus(200);
        $response->assertSee('Karyawan Demo');
        $response->assertDontSee('Petugas HSE');
    }

    /**
     * Filter berdasarkan status aktif/nonaktif akun berfungsi.
     */
    public function test_admin_can_filter_pengguna_by_status(): void
    {
        // Nonaktifkan karyawan demo terlebih dahulu
        $this->employeeUser->update(['is_active' => false]);

        // Filter aktif
        $resActive = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index', ['status' => '1']));
        $resActive->assertStatus(200);
        $resActive->assertSee('Petugas HSE');
        $resActive->assertDontSee('Karyawan Demo');

        // Filter nonaktif
        $resInactive = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index', ['status' => '0']));
        $resInactive->assertStatus(200);
        $resInactive->assertSee('Karyawan Demo');
        $resInactive->assertDontSee('Petugas HSE');
    }

    /**
     * Menampilkan pesan "Data tidak ditemukan" saat hasil pencarian kosong.
     */
    public function test_empty_state_is_displayed_when_no_users_match(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index', ['search' => 'NIK_TIDAK_ADA_9999']));

        $response->assertStatus(200);
        $response->assertSee('Data tidak ditemukan');
    }

    /**
     * Admin dapat memperbarui departemen, status kepegawaian, dan status akun pengguna.
     */
    public function test_admin_can_update_user_department_and_status(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.pengguna.update', $this->employeeUser), [
                'department_id' => $this->hseDepartment->id,
                'employment_status_id' => $this->permanentStatus->id,
                'is_active' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Data akun karyawan berhasil diperbarui.');

        $this->employeeUser->refresh();
        $this->assertEquals($this->hseDepartment->id, $this->employeeUser->department_id);
        $this->assertEquals($this->hseDepartment->id, $this->employeeUser->employeeRegistry->department_id);
        $this->assertEquals($this->permanentStatus->id, $this->employeeUser->employeeRegistry->employment_status_id);
        $this->assertTrue($this->employeeUser->is_active);
    }

    /**
     * Admin dapat menonaktifkan dan mengaktifkan kembali akun pengguna lain.
     */
    public function test_admin_can_deactivate_and_activate_user(): void
    {
        // 1. Nonaktifkan
        $responseDeactivate = $this->actingAs($this->adminUser)
            ->put(route('admin.pengguna.update', $this->employeeUser), [
                'department_id' => $this->employeeUser->department_id,
                'employment_status_id' => $this->permanentStatus->id,
                'is_active' => 0,
            ]);

        $responseDeactivate->assertRedirect();
        $responseDeactivate->assertSessionHas('success');
        $this->assertFalse($this->employeeUser->fresh()->is_active);

        // 2. Aktifkan kembali
        $responseActivate = $this->actingAs($this->adminUser)
            ->put(route('admin.pengguna.update', $this->employeeUser), [
                'department_id' => $this->employeeUser->department_id,
                'employment_status_id' => $this->permanentStatus->id,
                'is_active' => 1,
            ]);

        $responseActivate->assertRedirect();
        $responseActivate->assertSessionHas('success');
        $this->assertTrue($this->employeeUser->fresh()->is_active);
    }

    /**
     * Admin tidak boleh menonaktifkan akunnya sendiri.
     */
    public function test_admin_cannot_deactivate_own_account(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.pengguna.update', $this->adminUser), [
                'department_id' => $this->adminUser->department_id,
                'employment_status_id' => $this->permanentStatus->id,
                'is_active' => 0,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertTrue($this->adminUser->fresh()->is_active);
    }

    /**
     * Admin dapat menghapus (soft delete) akun pengguna dengan mencatat alasan dan pelaku.
     */
    public function test_admin_can_soft_delete_user_with_reason(): void
    {
        $reason = 'Karyawan telah mengundurkan diri (resign) per akhir bulan.';

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.pengguna.destroy', $this->employeeUser), [
                'deletion_reason' => $reason,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Akun karyawan berhasil dihapus.');

        $this->assertSoftDeleted('users', [
            'id' => $this->employeeUser->id,
            'deleted_by' => $this->adminUser->id,
            'deletion_reason' => $reason,
            'is_active' => false,
        ]);

        // NIK di tabel nik tidak terhapus (tetap tercatat)
        $this->assertDatabaseHas('nik', [
            'id' => $this->employeeUser->employee_registry_id,
            'nik' => 'DEMO003',
        ]);
    }

    /**
     * Soft delete akun tanpa alasan ditolak oleh validasi (UC-33 alternatif 5').
     */
    public function test_soft_delete_without_reason_is_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.pengguna.destroy', $this->employeeUser), [
                'deletion_reason' => '',
            ]);

        $response->assertSessionHasErrors('deletion_reason');
        $this->assertNotSoftDeleted('users', ['id' => $this->employeeUser->id]);
    }

    /**
     * Admin tidak boleh menghapus akunnya sendiri.
     */
    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.pengguna.destroy', $this->adminUser), [
                'deletion_reason' => 'Mencoba menghapus diri sendiri.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted('users', ['id' => $this->adminUser->id]);
    }

    /**
     * Akun yang di-soft-delete tidak tampil di daftar kelola karyawan.
     */
    public function test_soft_deleted_user_does_not_appear_in_list(): void
    {
        $this->employeeUser->update([
            'deleted_by' => $this->adminUser->id,
            'deletion_reason' => 'Keluar dari perusahaan.',
        ]);
        $this->employeeUser->delete();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pengguna.index'));

        $response->assertStatus(200);
        $response->assertDontSee('DEMO003');
    }

    /**
     * Akun yang di-soft-delete atau dinonaktifkan tidak dapat login ke sistem (FR-02).
     */
    public function test_inactive_and_deleted_users_cannot_login(): void
    {
        // 1. Akun nonaktif
        $this->employeeUser->update(['is_active' => false]);

        $resInactive = $this->post(route('login.submit'), [
            'login' => 'karyawan',
            'password' => 'DemoOnly!2026',
        ]);

        $this->assertGuest();
        $resInactive->assertSessionHasErrors('login');

        // 2. Akun terhapus (soft delete)
        $this->employeeUser->update(['is_active' => true]);
        $this->employeeUser->delete();

        $resDeleted = $this->post(route('login.submit'), [
            'login' => 'karyawan',
            'password' => 'DemoOnly!2026',
        ]);

        $this->assertGuest();
        $resDeleted->assertSessionHasErrors('login');
    }

    /**
     * Tombol simulasi reset password mengembalikan pesan informasi (stub PBI-08).
     */
    public function test_reset_password_stub_returns_info_message(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.pengguna.reset-password', $this->employeeUser));

        $response->assertRedirect();
        $response->assertSessionHas('info');
    }
}