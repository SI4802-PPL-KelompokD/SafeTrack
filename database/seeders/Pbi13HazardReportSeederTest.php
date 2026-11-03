<?php

namespace Tests\Feature;

use App\Models\HazardPhoto;
use App\Models\HazardReport;
use App\Models\HazardResolution;
use App\Models\HazardStatusHistory;
use App\Models\User;
use Database\Seeders\Pbi13HazardReportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Memastikan data demo PBI-13 lengkap, idempotent, dan benar-benar bisa dipakai di halaman PBI-13.
 */
class Pbi13HazardReportSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(Pbi13HazardReportSeeder::class);
    }

    public function test_seeder_creates_reports_in_every_status_including_archived(): void
    {
        $this->assertSame(27, HazardReport::withTrashed()->count());
        $this->assertSame(2, HazardReport::onlyTrashed()->count());

        foreach (array_keys(HazardReport::STATUS_LABELS) as $status) {
            $this->assertTrue(
                HazardReport::withTrashed()->where('status', $status)->exists(),
                "Tidak ada laporan berstatus {$status}"
            );
        }

        $rejected = HazardReport::where('title', 'Foto buram area gudang')->firstOrFail();
        $this->assertStringContainsString('Foto kurang jelas', $rejected->verification_notes);
    }

    public function test_seeder_creates_photos_histories_and_resolutions_with_files(): void
    {
        $this->assertGreaterThan(0, HazardPhoto::count());
        $this->assertGreaterThan(0, HazardStatusHistory::count());
        $this->assertGreaterThan(0, HazardResolution::count());

        Storage::disk('local')->assertExists('hazards/demo/temuan.png');
        Storage::disk('local')->assertExists('resolutions/demo/perbaikan.png');

        // laporan Proses milik karyawan: satu bukti ditolak + satu menunggu verifikasi
        $progress = HazardReport::where('title', 'Genangan oli di area mesin')->firstOrFail();
        $this->assertSame(['pending', 'rejected'], $progress->resolutions()->orderBy('status')->pluck('status')->all());
        $this->assertSame(
            ['pending_verification', 'open', 'in_progress'],
            $progress->histories()->pluck('to_status')->all()
        );
    }

    public function test_seeder_is_idempotent(): void
    {
        $counts = fn () => [
            HazardReport::withTrashed()->count(),
            HazardPhoto::count(),
            HazardStatusHistory::count(),
            HazardResolution::count(),
        ];
        $before = $counts();

        $this->seed(Pbi13HazardReportSeeder::class);

        $this->assertSame($before, $counts());
    }

    public function test_demo_data_works_on_the_pbi13_pages(): void
    {
        $karyawan = User::where('username', 'karyawan')->firstOrFail();
        $admin = User::where('username', 'admin')->firstOrFail();

        // Laporan Saya: semua status milik sendiri, tanpa laporan orang lain
        $this->actingAs($karyawan)->get(route('hazards.mine'))
            ->assertOk()
            ->assertSee('Kabel panel terkelupas di workshop')
            ->assertSee('Alasan: Foto kurang jelas')
            ->assertDontSee('Pagar pengaman kolam pengendapan rusak');

        // Daftar Pelaporan karyawan: laporan terverifikasi orang lain terlihat, yang pending/arsip tidak
        $this->actingAs($karyawan)->get(route('hazards.index'))
            ->assertOk()
            ->assertSee('Pagar pengaman kolam pengendapan rusak')
            ->assertDontSee('Tangga menuju ruang genset licin')
            ->assertDontSee('Laporan tidak lengkap: debu berlebih')
            ->assertDontSee('Temuan lama: tabung APAR kedaluwarsa')
            ->assertSee('page=2', false); // lebih dari 15 laporan => ada paginasi

        // Admin: semua laporan + arsip
        $this->actingAs($admin)->get(route('hazards.index'))
            ->assertOk()
            ->assertSee('Tangga menuju ruang genset licin');
        $this->actingAs($admin)->get(route('hazards.index', ['archive' => 1]))
            ->assertOk()
            ->assertSee('Temuan lama: tabung APAR kedaluwarsa')
            ->assertSee('Laporan duplikat: lampu jalan mati');

        // Detail lengkap dengan bukti perbaikan, riwayat, dan foto yang bisa dibuka
        $report = HazardReport::where('title', 'Genangan oli di area mesin')->firstOrFail();
        $this->actingAs($karyawan)->get(route('hazards.show', $report))
            ->assertOk()
            ->assertSee('Bukti perbaikan')
            ->assertSee('Menunggu verifikasi HSE')
            ->assertSee('Riwayat status');

        $photo = $report->photos()->firstOrFail();
        $this->actingAs($karyawan)->get(route('hazards.photos.show', [$report, $photo]))->assertOk();
    }
}
