<?php

namespace Tests\Browser;

use App\Models\Department;
use App\Models\HazardCategory;
use App\Models\HazardPhoto;
use App\Models\HazardReport;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\DuskTestCase;

/**
 * PBI-11 Pembuatan Laporan Bahaya (Jira ST-13) — automatic testing lewat browser (Laravel Dusk).
 *
 * Browser terbuka (tidak headless) dan setiap langkah diberi jeda (slow motion)
 * agar alur pengujian bisa diamati. Lama jeda diatur lewat DUSK_SLOWMO (milidetik) di .env.dusk.
 */
class Pbi11HazardReportBrowserTest extends DuskTestCase
{
    use DatabaseTruncation;

    private const PASSWORD = 'DemoOnly!2026';

    /** Gambar PNG 1x1 piksel yang valid, dipakai sebagai foto bukti. */
    private const PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADElEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC';

    private string $photoPath;

    private string $pdfPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);

        $directory = storage_path('app/dusk-fixtures');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->photoPath = $directory.DIRECTORY_SEPARATOR.'foto-bahaya.png';
        file_put_contents($this->photoPath, base64_decode(self::PNG_BASE64));

        $this->pdfPath = $directory.DIRECTORY_SEPARATOR.'dokumen-bukan-foto.pdf';
        file_put_contents($this->pdfPath, "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n");
    }

    /* ----------------------------------------------------------------- */
    /* Helper                                                            */
    /* ----------------------------------------------------------------- */

    /** Jeda slow motion antar langkah. */
    private function slow(Browser $browser): Browser
    {
        return $browser->pause((int) env('DUSK_SLOWMO', 700));
    }

    private function employee(): User
    {
        return User::where('username', 'karyawan')->firstOrFail();
    }

    private function login(Browser $browser): void
    {
        $browser->visit('/login');
        $this->slow($browser)->type('login', 'karyawan');
        $this->slow($browser)->type('password', self::PASSWORD);
        $this->slow($browser)->click('#btnSubmitLogin')->waitForLocation('/dashboard');
    }

    /** Membuka form lewat tombol "+ Buat Laporan" di menu Laporan Bahaya. */
    private function openForm(Browser $browser): void
    {
        $this->login($browser);
        $browser->visit('/laporan-bahaya')->waitFor('a[href$="/laporan-bahaya/create"]');
        $this->slow($browser)->click('a[href$="/laporan-bahaya/create"]')
            ->waitForLocation('/laporan-bahaya/create')
            ->waitForText('Buat Laporan Bahaya');
    }

    /** Mengisi semua isian dengan data valid. */
    private function fillValid(Browser $browser, string $title = 'Lantai licin di area produksi', bool $withPhoto = true): void
    {
        $category = HazardCategory::query()->where('is_active', true)->orderBy('name')->firstOrFail();
        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();
        $reporter = $departments->first();
        $repair = $departments->skip(1)->first() ?? $reporter;

        $this->slow($browser)->type('title', $title);
        $this->slow($browser)->type('location', 'Area Produksi A');
        $this->slow($browser)->type('description', 'Ditemukan tumpahan oli yang membuat lantai licin.');
        $this->slow($browser)->select('category_id', (string) $category->id);
        $this->slow($browser)->select('reporter_department_id', (string) $reporter->id);
        $this->slow($browser)->select('repair_department_id', (string) $repair->id);
        $this->slow($browser)->type('immediate_action', 'Memasang tanda peringatan dan menutup area.');

        if ($withPhoto) {
            $this->slow($browser)->attach('input[name="photos[]"]', $this->photoPath);
        }
    }

    private function submit(Browser $browser): Browser
    {
        return $this->slow($browser)->press('Kirim Laporan');
    }

    private function submitAndWaitSuccess(Browser $browser): void
    {
        $this->submit($browser)->waitForText('Laporan bahaya berhasil dibuat.');
    }

    /** Membuat sebuah pilihan dropdown berisi ID yang tidak ada di database. */
    private function selectUnregisteredOption(Browser $browser, string $field): void
    {
        $browser->script(
            "const s = document.querySelector('select[name=\"{$field}\"]');"
            ."const o = document.createElement('option'); o.value = '999999'; o.text = 'ID tidak terdaftar';"
            .'s.appendChild(o); s.value = "999999";'
        );
        $this->slow($browser);
    }

    /* ----------------------------------------------------------------- */
    /* Test case                                                         */
    /* ----------------------------------------------------------------- */

    /** TC-01: Karyawan dapat membuka halaman pembuatan laporan bahaya. */
    public function test_tc01_karyawan_membuka_form_laporan_bahaya(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);

            $browser->assertPathIs('/laporan-bahaya/create')
                ->assertPresent('input[name="title"]')
                ->assertPresent('input[name="location"]')
                ->assertPresent('textarea[name="description"]')
                ->assertPresent('select[name="category_id"]')
                ->assertPresent('select[name="reporter_department_id"]')
                ->assertPresent('select[name="repair_department_id"]')
                ->assertPresent('[name="immediate_action"]')
                ->assertPresent('input[name="photos[]"]')
                ->assertSee('Kirim Laporan');
            $this->evidence($this->slow($browser));
        });
    }

    /** TC-02: Laporan valid tersimpan berstatus open dan foto tersimpan. */
    public function test_tc02_laporan_valid_tersimpan_status_open_dengan_foto(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->evidence($browser, 'terisi');
            $this->submitAndWaitSuccess($browser);
            $this->evidence($browser, 'berhasil');

            $this->slow($browser)->visit('/laporan-saya')->assertSee('Lantai licin di area produksi');
            $this->evidence($this->slow($browser), 'laporan-saya');
        });

        $report = HazardReport::where('title', 'Lantai licin di area produksi')->firstOrFail();
        $this->assertSame(HazardReport::STATUS_OPEN, $report->status);
        $this->assertSame($this->employee()->id, $report->reporter_id);

        $photo = HazardPhoto::where('hazard_report_id', $report->id)->firstOrFail();
        $this->assertSame('local', $photo->disk);
        $this->assertTrue(Storage::disk('local')->exists($photo->path));
    }

    /** TC-03: Waktu pelaporan diambil dari waktu server. */
    public function test_tc03_waktu_lapor_menggunakan_waktu_server(): void
    {
        $start = now()->subSecond();

        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->submitAndWaitSuccess($browser);
            $this->evidence($this->slow($browser));
        });

        $report = HazardReport::firstOrFail();
        $this->assertGreaterThanOrEqual($start->timestamp, $report->reported_at->timestamp);
        $this->assertLessThanOrEqual(now()->addSecond()->timestamp, $report->reported_at->timestamp);
    }

    /** TC-05: Pembuatan laporan tercatat di audit log dengan aksi "tambah". */
    public function test_tc05_pembuatan_laporan_tercatat_di_audit_log(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->submitAndWaitSuccess($browser);
            $this->evidence($this->slow($browser));
        });

        $report = HazardReport::firstOrFail();
        $this->assertTrue(DB::table('audit_logs')->where([
            'actor_id' => $this->employee()->id,
            'action' => 'tambah',
            'entity_type' => 'hazard_report',
            'entity_id' => $report->id,
        ])->exists());
    }

    /** TC-06: Pengiriman ulang dengan client_request_id yang sama tidak membuat laporan ganda. */
    public function test_tc06_pengiriman_ganda_tidak_membuat_laporan_dobel(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->submitAndWaitSuccess($browser);
            $this->evidence($browser, 'kiriman-pertama');

            $uuid = HazardReport::firstOrFail()->client_request_id;

            $browser->visit('/laporan-bahaya/create')->waitForText('Buat Laporan Bahaya');
            $this->fillValid($browser);
            $browser->script("document.querySelector('input[name=\"client_request_id\"]').value = '{$uuid}';");
            $this->submit($browser)->waitForText('Laporan bahaya berhasil dibuat.');
            $this->evidence($this->slow($browser), 'kiriman-ulang');
        });

        $this->assertSame(1, HazardReport::count());
        $this->assertSame(1, HazardPhoto::count());
    }

    /** TC-07 s.d. TC-11: Isian wajib yang kosong ditolak (validasi browser), laporan tidak tersimpan. */
    #[TestWith(['title'])]
    #[TestWith(['location'])]
    #[TestWith(['description'])]
    #[TestWith(['category_id'])]
    #[TestWith(['reporter_department_id'])]
    #[TestWith(['repair_department_id'])]
    #[TestWith(['immediate_action'])]
    public function test_tc07_tc11_isian_wajib_kosong_ditolak(string $field): void
    {
        $this->browse(function (Browser $browser) use ($field): void {
            $this->openForm($browser);
            $this->fillValid($browser);

            $browser->script("document.querySelector('[name=\"{$field}\"]').value = '';");
            $this->submit($browser);

            $browser->assertPathIs('/laporan-bahaya/create')
                ->assertScript("document.querySelector('[name=\"{$field}\"]').validity.valueMissing", true);
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
    }

    /** TC-07 s.d. TC-11 (lanjutan): Kategori atau departemen yang tidak terdaftar ditolak oleh server. */
    #[TestWith(['category_id', 'Kategori temuan tidak ditemukan.'])]
    #[TestWith(['reporter_department_id', 'Departemen pelapor tidak ditemukan.'])]
    #[TestWith(['repair_department_id', 'Departemen perbaikan tidak ditemukan.'])]
    public function test_tc07_tc11_id_tidak_terdaftar_ditolak(string $field, string $message): void
    {
        $this->browse(function (Browser $browser) use ($field, $message): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->selectUnregisteredOption($browser, $field);
            $this->submit($browser)->waitForText($message);

            $browser->assertPathIs('/laporan-bahaya/create');
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
    }

    /** TC-12: Laporan tanpa foto ditolak. */
    public function test_tc12_laporan_tanpa_foto_ditolak(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser, withPhoto: false);
            $this->submit($browser);

            $browser->assertPathIs('/laporan-bahaya/create')
                ->assertScript("document.querySelector('input[name=\"photos[]\"]').validity.valueMissing", true);
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
    }

    /** TC-13: File PDF ditolak sebagai foto bukti. */
    public function test_tc13_file_pdf_ditolak_sebagai_foto(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser, withPhoto: false);
            $this->slow($browser)->attach('input[name="photos[]"]', $this->pdfPath);
            $this->submit($browser)->waitForText('File bukti harus berupa gambar.');

            $browser->assertPathIs('/laporan-bahaya/create');
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
        $this->assertSame(0, HazardPhoto::count());
    }

    /** TC-14: Pengguna yang belum login diarahkan ke halaman login. */
    public function test_tc14_tamu_diarahkan_ke_login(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->slow($browser)->visit('/laporan-bahaya/create')->waitForLocation('/login');

            $browser->assertPathIs('/login')->assertGuest();
            $this->evidence($this->slow($browser));
        });
    }
}