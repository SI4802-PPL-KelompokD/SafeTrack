<?php

namespace Tests\Feature;

use App\Models\HazardPhoto;
use App\Models\HazardReport;
use App\Models\HazardResolution;
use App\Models\HazardStatusHistory;
use App\Models\ResolutionPhoto;
use App\Models\User;
use App\Support\IdFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * PBI-13 — Laporan Saya, Daftar Pelaporan, Detail Laporan.
 */
class HazardReportViewingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $alice;
    private User $bob;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->alice = User::factory()->create();
        $this->bob = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Model::preventLazyLoading(false);

        parent::tearDown();
    }

    private function report(User $by, string $state, string $title, array $extra = []): HazardReport
    {
        return HazardReport::factory()->{$state}()->create(array_merge([
            'reporter_id' => $by->id,
            'title' => $title,
        ], $extra));
    }

    // ------------------------------------------------------------ Laporan Saya

    public function test_my_reports_list_only_own_reports_in_every_status(): void
    {
        $this->report($this->alice, 'pending', 'Milik Alice Pending');
        $this->report($this->alice, 'rejected', 'Milik Alice Ditolak', ['verification_notes' => 'Foto kurang jelas.']);
        $this->report($this->alice, 'closed', 'Milik Alice Selesai');
        $this->report($this->bob, 'open', 'Milik Bob Open');

        $this->actingAs($this->alice)->get(route('hazards.mine'))
            ->assertOk()
            ->assertSee('Laporan Saya')
            ->assertSee('Milik Alice Pending')
            ->assertSee('Milik Alice Ditolak')
            ->assertSee('Alasan: Foto kurang jelas.')
            ->assertSee('Milik Alice Selesai')
            ->assertSee('Menunggu Verifikasi')
            ->assertDontSee('Milik Bob Open');
    }

    public function test_my_reports_empty_state(): void
    {
        $this->actingAs($this->alice)->get(route('hazards.mine'))
            ->assertOk()
            ->assertSee('Anda belum membuat laporan');
    }

    // -------------------------------------------------------- Daftar Pelaporan

    public function test_employee_sees_own_reports_and_only_verified_reports_of_others(): void
    {
        $this->report($this->alice, 'pending', 'Alice Pending Terlihat');
        $this->report($this->bob, 'open', 'Bob Open Terlihat');
        $this->report($this->bob, 'inProgress', 'Bob Proses Terlihat');
        $this->report($this->bob, 'closed', 'Bob Close Terlihat');
        $this->report($this->bob, 'pending', 'Bob Pending Tersembunyi');
        $this->report($this->bob, 'rejected', 'Bob Ditolak Tersembunyi');

        $this->actingAs($this->alice)->get(route('hazards.index'))
            ->assertOk()
            ->assertSee('Alice Pending Terlihat')
            ->assertSee('Bob Open Terlihat')
            ->assertSee('Bob Proses Terlihat')
            ->assertSee('Bob Close Terlihat')
            ->assertDontSee('Bob Pending Tersembunyi')
            ->assertDontSee('Bob Ditolak Tersembunyi')
            ->assertDontSee('Arsip'); // tab arsip hanya untuk admin
    }

    public function test_admin_sees_all_reports_and_archive_tab_lists_only_archived(): void
    {
        $this->report($this->bob, 'pending', 'Bob Pending');
        $this->report($this->alice, 'open', 'Alice Open');
        $this->report($this->bob, 'archived', 'Bob Diarsipkan');

        $this->actingAs($this->admin)->get(route('hazards.index'))
            ->assertOk()
            ->assertSee('Bob Pending')
            ->assertSee('Alice Open')
            ->assertSee('Arsip')
            ->assertDontSee('Bob Diarsipkan');

        $this->actingAs($this->admin)->get(route('hazards.index', ['archive' => 1]))
            ->assertOk()
            ->assertSee('Bob Diarsipkan')
            ->assertDontSee('Alice Open');
    }

    public function test_employee_cannot_use_the_archive_tab(): void
    {
        $this->report($this->bob, 'archived', 'Bob Diarsipkan');
        $this->report($this->bob, 'open', 'Bob Open Aktif');

        $this->actingAs($this->alice)->get(route('hazards.index', ['archive' => 1]))
            ->assertOk()
            ->assertSee('Bob Open Aktif')
            ->assertDontSee('Bob Diarsipkan');
    }

    public function test_filters_work_alone_and_combined(): void
    {
        $today = Carbon::now('Asia/Jakarta')->setTime(12, 0)->utc();

        $a = $this->report($this->bob, 'open', 'Genangan oli di lantai', ['reported_at' => $today]);
        $this->report($this->alice, 'closed', 'Pagar rusak', ['reported_at' => $today->copy()->subDays(10)]);

        $viewer = $this->actingAs($this->alice);

        // pencarian judul
        $viewer->get(route('hazards.index', ['q' => 'Genangan']))
            ->assertSee('Genangan oli di lantai')->assertDontSee('Pagar rusak');

        // pencarian kode HZ-xxxx
        $viewer->get(route('hazards.index', ['q' => 'HZ-'.$a->id]))
            ->assertSee('Genangan oli di lantai')->assertDontSee('Pagar rusak');

        // pencarian nama pelapor
        $viewer->get(route('hazards.index', ['q' => $this->bob->name]))
            ->assertSee('Genangan oli di lantai')->assertDontSee('Pagar rusak');

        // status
        $viewer->get(route('hazards.index', ['status' => 'closed']))
            ->assertSee('Pagar rusak')->assertDontSee('Genangan oli di lantai');

        // departemen pelapor
        $viewer->get(route('hazards.index', ['department_id' => $this->bob->department_id]))
            ->assertSee('Genangan oli di lantai')->assertDontSee('Pagar rusak');

        // rentang tanggal (hari ini WIB)
        $day = IdFormat::today();
        $viewer->get(route('hazards.index', ['from' => $day, 'to' => $day]))
            ->assertSee('Genangan oli di lantai')->assertDontSee('Pagar rusak');

        // kombinasi: status + pencarian yang tidak cocok => kosong
        $viewer->get(route('hazards.index', ['q' => 'Genangan', 'status' => 'closed']))
            ->assertDontSee('Genangan oli di lantai')->assertDontSee('Pagar rusak')
            ->assertSee('Belum ada laporan');
    }

    public function test_search_treats_percent_and_underscore_literally(): void
    {
        $this->report($this->bob, 'open', 'Kebocoran pipa');

        $this->actingAs($this->alice)->get(route('hazards.index', ['q' => '%']))
            ->assertOk()->assertDontSee('Kebocoran pipa');
    }

    public function test_invalid_date_range_returns_a_validation_error(): void
    {
        $this->actingAs($this->alice)
            ->get(route('hazards.index', ['from' => '2026-10-10', 'to' => '2026-10-01']))
            ->assertSessionHasErrors('to');
    }

    public function test_pagination_keeps_filters(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            $this->report($this->bob, 'open', "Temuan nomor {$i}", ['location' => 'Gudang']);
        }

        $this->actingAs($this->alice)
            ->get(route('hazards.index', ['q' => 'Temuan']))
            ->assertOk()
            ->assertSee('16 laporan')
            ->assertSee('q=Temuan', false)
            ->assertSee('page=2', false);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $report = $this->report($this->bob, 'open', 'Terbuka');

        $this->get(route('hazards.index'))->assertRedirect(route('login'));
        $this->get(route('hazards.mine'))->assertRedirect(route('login'));
        $this->get(route('hazards.show', $report))->assertRedirect(route('login'));
    }

    // ----------------------------------------------------------------- Detail

    public function test_detail_visibility_rules(): void
    {
        $alicePending = $this->report($this->alice, 'pending', 'Alice Pending');
        $bobPending = $this->report($this->bob, 'pending', 'Bob Pending');
        $bobRejected = $this->report($this->bob, 'rejected', 'Bob Ditolak');
        $bobOpen = $this->report($this->bob, 'open', 'Bob Open');
        $bobArchived = $this->report($this->bob, 'archived', 'Bob Arsip');
        $aliceArchived = $this->report($this->alice, 'archived', 'Alice Arsip');

        // karyawan
        $this->actingAs($this->alice);
        $this->get(route('hazards.show', $alicePending))->assertOk();
        $this->get(route('hazards.show', $bobOpen))->assertOk();
        $this->get(route('hazards.show', $bobPending))->assertNotFound();
        $this->get(route('hazards.show', $bobRejected))->assertNotFound();
        $this->get(route('hazards.show', $bobArchived))->assertNotFound();
        $this->get(route('hazards.show', $aliceArchived))->assertNotFound();

        // admin melihat semuanya, termasuk arsip
        $this->actingAs($this->admin);
        foreach ([$alicePending, $bobPending, $bobRejected, $bobOpen, $bobArchived] as $report) {
            $this->get(route('hazards.show', $report))->assertOk();
        }
    }

    public function test_detail_renders_all_sections(): void
    {
        $report = $this->report($this->bob, 'inProgress', 'Kabel terkelupas', [
            'location' => 'Workshop Utama',
            'description' => 'Kabel panel terkelupas dan berbahaya.',
            'immediate_action' => 'Area diberi pembatas.',
            'risk_level' => 'high',
        ]);

        HazardPhoto::create([
            'hazard_report_id' => $report->id, 'uploaded_by' => $this->bob->id, 'disk' => 'local',
            'path' => 'hazards/temuan.jpg', 'original_name' => 'temuan.jpg', 'mime_type' => 'image/jpeg', 'size_bytes' => 10,
        ]);

        HazardStatusHistory::create([
            'hazard_report_id' => $report->id, 'from_status' => null, 'to_status' => 'pending_verification',
            'actor_id' => $this->bob->id, 'reason' => 'Laporan dikirim',
        ]);
        HazardStatusHistory::create([
            'hazard_report_id' => $report->id, 'from_status' => 'pending_verification', 'to_status' => 'open',
            'actor_id' => $this->admin->id, 'reason' => 'Disetujui HSE',
        ]);

        $resolution = HazardResolution::create([
            'hazard_report_id' => $report->id, 'submitted_by' => $this->bob->id,
            'department_id' => $this->bob->department_id, 'client_request_id' => (string) Str::uuid(),
            'description' => 'Kabel sudah diisolasi dan dirapikan.', 'status' => 'pending', 'submitted_at' => now(),
        ]);
        ResolutionPhoto::create([
            'hazard_resolution_id' => $resolution->id, 'uploaded_by' => $this->bob->id, 'disk' => 'local',
            'path' => 'resolutions/bukti.jpg', 'original_name' => 'bukti.jpg', 'mime_type' => 'image/jpeg', 'size_bytes' => 10,
        ]);

        $this->actingAs($this->alice)->get(route('hazards.show', $report))
            ->assertOk()
            ->assertSee($report->code())
            ->assertSee('Kabel terkelupas')
            ->assertSee('Proses')
            ->assertSee('Risiko Tinggi')
            ->assertSee('Workshop Utama')
            ->assertSee('Kabel panel terkelupas dan berbahaya.')
            ->assertSee('Area diberi pembatas.')
            ->assertSee('Foto temuan')
            ->assertSee('Bukti perbaikan')
            ->assertSee('Kabel sudah diisolasi dan dirapikan.')
            ->assertSee('Menunggu verifikasi HSE')
            ->assertSee('Riwayat status')
            ->assertSee('Laporan dikirim')
            ->assertSee('Disetujui HSE')
            ->assertSee('Belum ditetapkan')
            ->assertDontSee('Admin penanggung jawab');

        $this->actingAs($this->admin)->get(route('hazards.show', $report))
            ->assertOk()
            ->assertSee('Admin penanggung jawab')
            ->assertSee('Diverifikasi oleh');
    }

    public function test_rejected_report_shows_the_rejection_reason_to_its_owner(): void
    {
        $report = $this->report($this->alice, 'rejected', 'Ditolak HSE', ['verification_notes' => 'Lokasi tidak jelas.']);

        $this->actingAs($this->alice)->get(route('hazards.show', $report))
            ->assertOk()
            ->assertSee('Laporan ditolak.')
            ->assertSee('Lokasi tidak jelas.');
    }

    // ------------------------------------------------------------------ Foto

    public function test_photos_follow_report_visibility(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('hazards/a.jpg', 'fake-image-bytes');

        $open = $this->report($this->bob, 'open', 'Terbuka');
        $pending = $this->report($this->bob, 'pending', 'Pending');

        $photo = fn (HazardReport $r) => HazardPhoto::create([
            'hazard_report_id' => $r->id, 'uploaded_by' => $this->bob->id, 'disk' => 'local',
            'path' => 'hazards/a.jpg', 'original_name' => 'a.jpg', 'mime_type' => 'image/jpeg', 'size_bytes' => 16,
        ]);
        $openPhoto = $photo($open);
        $pendingPhoto = $photo($pending);

        $this->actingAs($this->alice)->get(route('hazards.photos.show', [$open, $openPhoto]))
            ->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        // laporan pending milik orang lain: 404
        $this->actingAs($this->alice)->get(route('hazards.photos.show', [$pending, $pendingPhoto]))->assertNotFound();

        // foto milik laporan lain tidak bisa dibuka lewat laporan yang boleh dilihat
        $this->actingAs($this->alice)->get(route('hazards.photos.show', [$open, $pendingPhoto]))->assertNotFound();

        // admin boleh
        $this->actingAs($this->admin)->get(route('hazards.photos.show', [$pending, $pendingPhoto]))->assertOk();
    }

    public function test_resolution_photos_follow_report_visibility(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('resolutions/b.jpg', 'fake-image-bytes');

        $report = $this->report($this->bob, 'inProgress', 'Sedang diperbaiki');
        $resolution = HazardResolution::create([
            'hazard_report_id' => $report->id, 'submitted_by' => $this->bob->id,
            'department_id' => $this->bob->department_id, 'client_request_id' => (string) Str::uuid(),
            'description' => 'Selesai.', 'status' => 'pending', 'submitted_at' => now(),
        ]);
        $photo = ResolutionPhoto::create([
            'hazard_resolution_id' => $resolution->id, 'uploaded_by' => $this->bob->id, 'disk' => 'local',
            'path' => 'resolutions/b.jpg', 'original_name' => 'b.jpg', 'mime_type' => 'image/jpeg', 'size_bytes' => 16,
        ]);

        $this->actingAs($this->alice)->get(route('hazards.resolution-photos.show', [$report, $photo]))->assertOk();

        $other = $this->report($this->bob, 'open', 'Laporan lain');
        $this->actingAs($this->alice)->get(route('hazards.resolution-photos.show', [$other, $photo]))->assertNotFound();
    }

    // -------------------------------------------------------------- Performa

    public function test_list_pages_do_not_lazy_load_relations(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->report(User::factory()->create(), 'open', "Temuan {$i}");
        }
        $this->report($this->alice, 'pending', 'Milik Alice');

        Model::preventLazyLoading(true);

        $this->actingAs($this->alice)->get(route('hazards.index'))->assertOk();
        $this->actingAs($this->alice)->get(route('hazards.mine'))->assertOk();
        $this->actingAs($this->admin)->get(route('hazards.index'))->assertOk();
    }
}
