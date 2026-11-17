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
 * Nomor, nama, input, dan output setiap test mengikuti tabel Test Case PBI-11 (TC-01 s.d. TC-14).
 * Browser terbuka (tidak headless) dan setiap langkah diberi jeda (slow motion) agar alur pengujian
 * bisa diamati. Lama jeda diatur lewat DUSK_SLOWMO (milidetik) di .env.dusk.
 *
 * Catatan penamaan ERD -> database:
 * - hazard_reports.foto_bukti  -> tabel hazard_photos (kolom path)
 * - hazard_reports.waktu_lapor -> hazard_reports.reported_at
 * - audit_log.user_id / entity_name -> audit_logs.actor_id / entity_type
 */
class Pbi11HazardReportBrowserTest extends DuskTestCase
{
    use DatabaseTruncation;

    private const PASSWORD = 'DemoOnly!2026';

    /** Data input TC-02 sesuai tabel skenario normal. */
    private const JUDUL = 'Kabel terkelupas di area produksi';

    private const LOKASI = 'Gedung A lantai 1';

    private const DESKRIPSI = 'Kabel mesin press terkelupas dekat jalur pejalan kaki';

    private const TINDAKAN = 'Memasang garis pembatas';

    /** Gambar JPEG kecil yang valid, disimpan sebagai rak-rusak.jpg. */
    private const JPG_BASE64 = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAoHBwgHBgoICAgLCgoLDhgQDg0NDh0VFhEYIx8lJCIfIiEmKzcvJik0KSEiMEExNDk7Pj4+JS5ESUM8SDc9Pjv/2wBDAQoLCw4NDhwQEBw7KCIoOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozv/wAARCAAIAAgDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwDJooorxD7o/9k=';

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

        $this->photoPath = $directory.DIRECTORY_SEPARATOR.'rak-rusak.jpg';
        file_put_contents($this->photoPath, base64_decode(self::JPG_BASE64));

        $this->pdfPath = $directory.DIRECTORY_SEPARATOR.'dokumen.pdf';
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

    /** Mengisi semua isian dengan data valid TC-02. */
    private function fillValid(Browser $browser, bool $withPhoto = true): void
    {
        $category = HazardCategory::query()->where('is_active', true)->orderBy('name')->firstOrFail();
        $reporterDepartment = $this->employee()->department_id;
        $repairDepartment = Department::query()->where('is_active', true)
            ->where('id', '!=', $reporterDepartment)->orderBy('name')->value('id') ?? $reporterDepartment;

        $this->slow($browser)->type('title', self::JUDUL);
        $this->slow($browser)->type('location', self::LOKASI);
        $this->slow($browser)->type('description', self::DESKRIPSI);
        $this->slow($browser)->select('category_id', (string) $category->id);
        $this->slow($browser)->select('reporter_department_id', (string) $reporterDepartment);
        $this->slow($browser)->select('repair_department_id', (string) $repairDepartment);
        $this->slow($browser)->type('immediate_action', self::TINDAKAN);

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

    /**
     * Mematikan validasi bawaan browser agar data benar-benar dikirim ke server,
     * sehingga yang diuji adalah validasi sistem beserta pesan kesalahannya.
     */
    private function disableBrowserValidation(Browser $browser): void
    {
        $browser->script("document.querySelector('form[action$=\"/laporan-bahaya\"]').noValidate = true;");
    }

    /** Mengosongkan satu isian (teks atau pilihan). */
    private function clearField(Browser $browser, string $field): void
    {
        $browser->script("document.querySelector('[name=\"{$field}\"]').value = '';");
        $this->slow($browser);
    }

    /** Memilih ID yang tidak ada di database (99999) pada sebuah dropdown. */
    private function selectUnregisteredOption(Browser $browser, string $field): void
    {
        $browser->script(
            "const s = document.querySelector('select[name=\"{$field}\"]');"
            ."const o = document.createElement('option'); o.value = '99999'; o.text = 'ID 99999 (tidak terdaftar)';"
            .'s.appendChild(o); s.value = "99999";'
        );
        $this->slow($browser);
    }

    /* ----------------------------------------------------------------- */
    /* Skenario normal                                                   */
    /* ----------------------------------------------------------------- */

    /** TC-01 (AC1): Karyawan membuka form laporan bahaya. */
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
                ->assertPresent('textarea[name="immediate_action"]')
                ->assertPresent('input[name="photos[]"]')
                ->assertSee('Kirim Laporan');

            // Pilihan kategori dan departemen berisi data master.
            $categories = HazardCategory::query()->where('is_active', true)->count();
            $departments = Department::query()->where('is_active', true)->count();
            $this->assertGreaterThan(0, $categories);
            $this->assertGreaterThan(0, $departments);
            $browser->assertScript("document.querySelectorAll('select[name=\"category_id\"] option[value]:not([value=\"\"])').length", $categories)
                ->assertScript("document.querySelectorAll('select[name=\"reporter_department_id\"] option[value]:not([value=\"\"])').length", $departments)
                ->assertScript("document.querySelectorAll('select[name=\"repair_department_id\"] option[value]:not([value=\"\"])').length", $departments);

            $this->evidence($this->slow($browser));
        });
    }

    /** TC-02 (AC2): Karyawan mengirim laporan valid dengan foto bukti. */
    public function test_tc02_karyawan_mengirim_laporan_valid_dengan_foto_bukti(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->evidence($browser, 'terisi');
            $this->submitAndWaitSuccess($browser);
            $this->evidence($browser, 'berhasil');

            $this->slow($browser)->visit('/laporan-saya')->assertSee(self::JUDUL);
            $this->evidence($this->slow($browser), 'laporan-saya');
        });

        $this->assertSame(1, HazardReport::count());
        $report = HazardReport::firstOrFail();
        $this->assertSame(HazardReport::STATUS_OPEN, $report->status);
        $this->assertSame($this->employee()->id, $report->reporter_id);
        $this->assertSame(self::JUDUL, $report->title);

        // Foto bukti: path file tersimpan dan file benar-benar ada di storage.
        $photo = HazardPhoto::where('hazard_report_id', $report->id)->firstOrFail();
        $this->assertSame('rak-rusak.jpg', $photo->original_name);
        $this->assertTrue(Storage::disk('local')->exists($photo->path));
    }

    /** TC-03 (AC2): Waktu laporan memakai waktu server, bukan waktu dari client. */
    public function test_tc03_waktu_laporan_memakai_waktu_server(): void
    {
        $start = now()->subSecond();

        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);

            // Client mencoba mengirim waktu_lapor = 2020-01-01 00:00:00.
            $browser->script(
                "const f = document.querySelector('form[action$=\"/laporan-bahaya\"]');"
                ."['reported_at', 'waktu_lapor'].forEach(n => { const i = document.createElement('input');"
                ."i.type = 'hidden'; i.name = n; i.value = '2020-01-01 00:00:00'; f.appendChild(i); });"
            );

            $this->submitAndWaitSuccess($browser);
            $this->evidence($this->slow($browser));
        });

        $report = HazardReport::firstOrFail();
        $this->assertNotSame('2020-01-01', $report->reported_at->toDateString());
        $this->assertGreaterThanOrEqual($start->timestamp, $report->reported_at->timestamp);
        $this->assertLessThanOrEqual(now()->addSecond()->timestamp, $report->reported_at->timestamp);
    }

    /** TC-04 (AC5): Notifikasi laporan baru terkirim ke Admin/HSE. */
    public function test_tc04_notifikasi_laporan_baru_terkirim_ke_admin_hse(): void
    {
        $this->markTestSkipped('TC-04 bergantung pada PBI-26 Notifikasi Otomatis (jadwal 8–16 Des 2026); fitur notifikasi belum tersedia.');
    }

    /** TC-05 (AC5): Penambahan laporan tercatat di audit log. */
    public function test_tc05_penambahan_laporan_tercatat_di_audit_log(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->submitAndWaitSuccess($browser);
            $this->evidence($this->slow($browser));
        });

        $report = HazardReport::firstOrFail();
        $this->assertSame(1, DB::table('audit_logs')->where([
            'actor_id' => $this->employee()->id,   // ERD: user_id
            'action' => 'tambah',
            'entity_type' => 'hazard_report',     // ERD: entity_name
            'entity_id' => $report->id,
        ])->count());
    }

    /** TC-06 (AC6): Pengiriman ulang draf offline tidak membuat data ganda. */
    public function test_tc06_pengiriman_ulang_draf_offline_tidak_membuat_data_ganda(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->submitAndWaitSuccess($browser);
            $this->evidence($browser, 'kiriman-pertama');

            // Draf yang sama (client_request_id sama) dikirim ulang dua kali.
            $uuid = HazardReport::firstOrFail()->client_request_id;
            foreach ([1, 2] as $ulang) {
                $browser->visit('/laporan-bahaya/create')->waitForText('Buat Laporan Bahaya');
                $this->fillValid($browser);
                $browser->script("document.querySelector('input[name=\"client_request_id\"]').value = '{$uuid}';");
                $this->submitAndWaitSuccess($browser);
                $this->evidence($this->slow($browser), 'kiriman-ulang-'.$ulang);
            }
        });

        $this->assertSame(1, HazardReport::count());
        $this->assertSame(1, HazardPhoto::count());
    }

    /* ----------------------------------------------------------------- */
    /* Skenario exception                                                */
    /* ----------------------------------------------------------------- */

    /** TC-07 (AC3): Judul kosong. */
    public function test_tc07_judul_kosong(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->disableBrowserValidation($browser);
            $this->clearField($browser, 'title');
            $this->submit($browser)->waitForText('Judul laporan wajib diisi.');

            $browser->assertPathIs('/laporan-bahaya/create');
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
    }

    /** TC-08 (AC3): Lokasi, deskripsi, dan kategori kosong. */
    public function test_tc08_lokasi_deskripsi_dan_kategori_kosong(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->disableBrowserValidation($browser);
            $this->clearField($browser, 'location');
            $this->clearField($browser, 'description');
            $this->clearField($browser, 'category_id');
            $this->submit($browser)->waitForText('Lokasi kejadian wajib diisi.');

            $browser->assertSee('Deskripsi kejadian wajib diisi.')
                ->assertSee('Kategori temuan wajib dipilih.')
                ->assertPathIs('/laporan-bahaya/create');
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
    }

    /** TC-09 (AC3): Tindakan kosong. */
    public function test_tc09_tindakan_kosong(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->disableBrowserValidation($browser);
            $this->clearField($browser, 'immediate_action');
            $this->submit($browser)->waitForText('Tindakan sementara wajib diisi.');

            $browser->assertPathIs('/laporan-bahaya/create');
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
    }

    /** TC-10 (AC3): Departemen pelapor atau perbaikan tidak dipilih. */
    #[TestWith(['reporter_department_id', 'Departemen pelapor wajib dipilih.'])]
    #[TestWith(['repair_department_id', 'Departemen perbaikan wajib dipilih.'])]
    public function test_tc10_departemen_pelapor_atau_perbaikan_kosong(string $field, string $message): void
    {
        $this->browse(function (Browser $browser) use ($field, $message): void {
            $this->openForm($browser);
            $this->fillValid($browser);
            $this->disableBrowserValidation($browser);
            $this->clearField($browser, $field);
            $this->submit($browser)->waitForText($message);

            $browser->assertPathIs('/laporan-bahaya/create');
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
    }

    /** TC-11 (AC3): Kategori atau departemen tidak terdaftar (ID 99999). */
    #[TestWith(['category_id', 'Kategori temuan tidak ditemukan.'])]
    #[TestWith(['reporter_department_id', 'Departemen pelapor tidak ditemukan.'])]
    #[TestWith(['repair_department_id', 'Departemen perbaikan tidak ditemukan.'])]
    public function test_tc11_kategori_atau_departemen_tidak_terdaftar(string $field, string $message): void
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

    /** TC-12 (AC3): Laporan tanpa foto bukti. */
    public function test_tc12_laporan_tanpa_foto_bukti(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser, withPhoto: false);
            $this->disableBrowserValidation($browser);
            $this->submit($browser)->waitForText('Foto bukti wajib');

            $browser->assertPathIs('/laporan-bahaya/create');
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
    }

    /** TC-13 (AC4): File foto bukti bukan gambar (dokumen.pdf). */
    public function test_tc13_file_foto_bukti_bukan_gambar(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openForm($browser);
            $this->fillValid($browser, withPhoto: false);
            $this->slow($browser)->attach('input[name="photos[]"]', $this->pdfPath);
            $this->submit($browser)->waitForText('File bukti harus berupa gambar.');

            // Karyawan dapat mengunggah ulang: input foto tetap tersedia.
            $browser->assertPathIs('/laporan-bahaya/create')
                ->assertPresent('input[name="photos[]"]');
            $this->evidence($this->slow($browser));
        });

        $this->assertSame(0, HazardReport::count());
        $this->assertSame(0, HazardPhoto::count());
    }

    /** TC-14 (Precondition): Pengguna belum login mengirim data. */
    public function test_tc14_pengguna_belum_login(): void
    {
        $this->browse(function (Browser $browser): void {
            // Membuka form tanpa login diarahkan ke halaman login.
            $this->slow($browser)->visit('/laporan-bahaya/create')->waitForLocation('/login');
            $browser->assertPathIs('/login')->assertGuest();
            $this->evidence($browser, 'buka-form');

            // Mengirim data laporan tanpa login juga diarahkan ke halaman login.
            $browser->script(
                "const f = document.createElement('form'); f.method = 'POST'; f.action = '/laporan-bahaya';"
                ."const t = document.querySelector('input[name=\"_token\"]');"
                ."[['_token', t ? t.value : ''], ['title', 'Kabel terkelupas di area produksi']].forEach(([n, v]) => {"
                ."const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; f.appendChild(i); });"
                .'document.body.appendChild(f); f.submit();'
            );
            $this->slow($browser)->waitForLocation('/login')->assertPathIs('/login')->assertGuest();
            $this->evidence($this->slow($browser), 'kirim-data');
        });

        $this->assertSame(0, HazardReport::count());
    }
}