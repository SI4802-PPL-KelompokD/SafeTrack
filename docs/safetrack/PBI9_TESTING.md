# Pengujian PBI-9: Kelola Karyawan

Panduan menjalankan ulang Laravel Dusk melalui Selenium Standalone pada Windows. ID `TC10-xx` dan `TS10-xx` dipertahankan sesuai dokumen awal; fitur yang diuji sekarang bernomor **PBI-9**.

## Lingkungan yang disiapkan

- Laravel 13.39.0, Laravel Dusk 8.8.0, php-webdriver 1.16.0, PHPUnit 12.5.43.
- Selenium Standalone 4.52.0; Java tersedia pada PATH.
- Browser utama: Microsoft Edge. Pengujian ini memakai Edge 157.0.4341.46. Versi browser/driver aktual setiap run disimpan dalam `browser-capabilities.json`.
- PHP CLI pada komputer saat pengujian adalah **8.3.24**. Keterangan PHP 8.5 dalam instruksi project belum merupakan hasil pengujian pada runtime 8.5.
- MySQL berjalan; kredensial lokal di `.env` memiliki hak membuat database pengujian.
- PHP, Composer, Java, dan curl tersedia pada PATH; frontend sudah dibangun.

Dependensi Dusk berada di `require-dev`. Sebelum pengujian pertama pada checkout baru:

```powershell
composer install
npm.cmd ci
npm.cmd run build
php artisan config:clear
```

`composer.lock` sebelumnya memiliki penanda konflik merge. Konflik diselesaikan memakai versi yang sama dengan seluruh paket yang telah terpasang, lalu Composer menambahkan Dusk dan php-webdriver. Tidak diperlukan instalasi Selenium Python atau Selenium IDE.

## Menjalankan seluruh pengujian

Dari root project:

```powershell
php scripts/test-pbi9.php
```

Runner akan:

1. Membuat `.env.dusk` jika belum ada, menggunakan kredensial MySQL dari `.env` dan APP_KEY khusus pengujian.
2. Membuat database `safetrack_test` dan `safetrack_dusk` jika belum tersedia.
3. Menjalankan suite feature/integrasi pada `safetrack_test`.
4. Menyalakan Selenium di `127.0.0.1:4445` dan server aplikasi uji di `127.0.0.1:8019`.
5. Menjalankan Dusk dengan jendela Edge terlihat. Selenium Manager mengunduh driver yang diperlukan pada penggunaan pertama.
6. Menyimpan hasil dan bukti dalam folder bertanggal, lalu menghentikan proses layanan yang dinyalakan runner.

Jika Selenium sudah siap pada port 4445, runner memakainya dan membiarkannya tetap berjalan setelah pengujian. Port 8019 harus tersedia; server aplikasi biasa pada port 8000 dapat tetap berjalan.

Runner menggunakan PHPUnit langsung untuk mengeksekusi kelas Dusk. `.env` aplikasi tidak ditukar dengan `.env.dusk`. Ini menghindari perubahan sementara konfigurasi server pengembangan yang sedang berjalan.

Database pengujian direset selama eksekusi. Guard pada `Tests\TestCase`, `Tests\DuskTestCase`, dan fixture seeder membatasi penggunaan ke nama database pengujian tersebut. `.env.dusk` berisi konfigurasi lokal dan diabaikan Git; template kosong ada di `.env.dusk.example`.

## Pilihan eksekusi

```powershell
# Semua kasus tanpa jendela browser terlihat
php scripts/test-pbi9.php --headless

# Hanya browser/Dusk melalui Selenium
php scripts/test-pbi9.php --browser-only

# Hanya feature dan integrasi
php scripts/test-pbi9.php --feature-only

# Ulangi kasus sesi lama K1
php scripts/test-pbi9.php --browser-only --filter=tc10_22

# Ulangi kasus validasi departemen
php scripts/test-pbi9.php --feature-only --filter=tc10_07

# Chrome, jika Chrome tersedia dan ingin digunakan
php scripts/test-pbi9.php --browser=chrome
```

Mode Edge dengan jendela terlihat adalah mode yang diverifikasi. Hasil untuk filter atau suite tertentu hanya mewakili subset yang dijalankan. Keluar dengan kode `0` berarti perintah pengujian berhasil; kode selain `0` perlu diperiksa pada laporan/log.

## Fixture terisolasi

Fixture dibuat oleh `database/seeders/Pbi9EmployeeFixtureSeeder.php` pada setiap kasus. Browser memakai `DatabaseTruncation`, sesi database, dan browser baru untuk setiap kasus. Feature/integrasi memakai `RefreshDatabase`. Akun dari database aplikasi dan NIK `EMP-TEST-*` untuk percobaan manual tidak menjadi fixture suite ini.

| Alias | Kondisi awal |
|---|---|
| H1 | `h1`, Petugas HSE Penguji, peran admin, aktif |
| H2 | `h2`, Admin Utama Penguji, peran super_admin, aktif; target terlindungi tambahan |
| K1 | `k1`, Aditya Pratama, EMP-001, aditya@example.test, Operations, aktif, belum dihapus |
| K2 | `k2`, Nabila Anindya, EMP-003, nabila@example.test, HCGS, nonaktif, belum dihapus |
| K3 | `k3`, karyawan aktif yang memiliki satu hazard report |
| KD | `kd`, akun soft delete dengan alasan dan pelaku penghapusan lama |
| KX/DX | ID 999999 yang tidak terdapat pada fixture |

Password fixture: `Pbi9Only!2026`, hanya untuk akun uji pada database pengujian. Departemen tujuan perubahan: Engineering. Status kepegawaian awal: Karyawan Tetap. Data baru dibuat ulang pada setiap variasi, sehingga hasil kasus sebelumnya tidak menjadi prasyarat kasus berikutnya.

## Cakupan dan pemetaan

Implementasi lengkap berisi **35 eksekusi feature/integrasi dan 21 eksekusi browser**, total **56 eksekusi untuk 22 test case**. Variasi input/mutasi menjadi eksekusi terpisah, meskipun beberapa memakai satu metode dengan data provider.

| Test case asli | Scenario | Pemeriksaan dan lapisan |
|---|---|---|
| TC10-01 | TS10-01 | Dusk: K1/K2 tampil; H1/H2/KD tidak berada dalam tabel |
| TC10-02 | TS10-02 | Dusk: pencarian nama, NIK, email; tiga kasus terpisah |
| TC10-03 | TS10-02 | Dusk: pencarian tanpa kecocokan |
| TC10-04 | TS10-03 | Dusk: filter Operations dan Nonaktif secara terpisah |
| TC10-05 | TS10-03 | Dusk: Nabila + HCGS + Nonaktif, lalu semua kriteria dikosongkan |
| TC10-06 | TS10-04 | Dusk dan database: Operations menjadi Engineering; reload; field lain tetap |
| TC10-07 | TS10-04 | Feature: departemen kosong dan DX ditolak 422; seluruh data tetap |
| TC10-08 | TS10-05 | Dusk dan database: K1 aktif menjadi nonaktif |
| TC10-09 | TS10-05 | Dusk dan database: K2 nonaktif menjadi aktif |
| TC10-10 | TS10-06 | Dusk login nyata: kredensial K2 benar, login ditolak, tidak ada sesi autentikasi baru |
| TC10-11 | TS10-07 | Dusk: Batal pada pengelolaan dan dialog hapus; data/metadata tidak berubah |
| TC10-12 | TS10-08 | Dusk + feature: alasan kosong dan hanya spasi ditolak, masing-masing terpisah |
| TC10-13 | TS10-08 | Dusk dan database: soft delete, alasan, ID H1, waktu server, hilang setelah reload |
| TC10-14 | TS10-09 | Integrasi: record laporan utuh dan relasi reporter tetap mengacu ke K3 yang soft delete |
| TC10-15 | TS10-10 | Feature: karyawan ditolak 403 untuk daftar dan empat jenis mutasi |
| TC10-16 | TS10-10 | Feature: guest daftar diarahkan login; mutasi JSON ditolak 401; data tetap |
| TC10-17 | TS10-11 | Feature: target H1/H2 ditolak 422 untuk departemen, nonaktif, aktif, hapus |
| TC10-18 | TS10-11 | Feature: KX/KD ditolak 404 untuk setiap mutasi; metadata lama dan akun lain tetap |
| TC10-19 | TS10-12 | Integrasi: fault departemen, nonaktif, aktif, hapus; Dusk: pesan gagal terlihat |
| TC10-20 | TS10-01 | Dusk: daftar karyawan kosong, pesan kosong dan jumlah 0 |
| TC10-21 | TS10-06 | Dusk login nyata: K1 soft delete ditolak meskipun password sebelumnya benar |
| TC10-22 | TS10-06 | Dusk dua sesi: H1 menonaktifkan K1, request K1 berikutnya ditolak dan diarahkan login; sesi K1 berakhir; H1 tetap login |

Nama metode mengandung ID test case. Metode gabungan `tc10_08_tc10_09` memakai kasus K1/K2 terpisah; `tc10_10_tc10_21` memakai K2 nonaktif/K1 terhapus; `tc10_17_tc10_18` memakai nama dataset H1/H2/KX/KD. Laporan JUnit menyimpan masing-masing variasi sebagai test case tersendiri.

Untuk fault penyimpanan, suite feature melempar exception pada batas penyimpanan registry setelah perubahan pengguna dimulai. Snapshot seluruh record users/nik dibandingkan sebelum/sesudah untuk membuktikan rollback. Dusk juga memasang trigger MySQL sementara yang menggagalkan UPDATE registry untuk membuktikan rollback nyata dan pesan kesalahan UI. Trigger dibersihkan dalam `finally`; fixture dan guard membatasi operasinya ke database uji.

TC10-22 sudah final dan tidak pending: **request berikutnya ditolak, sesi K1 diakhiri, pengguna diarahkan ke login**. Pemeriksaan menggunakan tabel sesi dari `config('session.table')`; pada project ini tabelnya `user_sessions`.

## Bukti dan laporan

Setiap run menghasilkan folder:

```text
storage/app/pbi9-evidence/YYYYMMDD-HHMMSS/
```

Lokasi run terakhir tertulis di `storage/app/pbi9-evidence/latest.txt`.

| Berkas | Isi |
|---|---|
| `report.html` | Tabel hasil, perintah, tautan artefak dan galeri screenshot yang dapat dibuka |
| `report.md` | Passed/failed/skipped, SHA commit dan hasil setiap kasus |
| `feature.xml`, `browser.xml` | JUnit XML untuk hasil/assertion dan rincian kegagalan |
| `feature.log`, `browser.log` | Output runner pengujian |
| `selenium.log`, `application.log` | Log Selenium dan HTTP server uji |
| `metadata.json` | Waktu Asia/Jakarta, PHP, mode browser, filter, exit code dan hitungan hasil |
| `browser-capabilities.json` | Versi browser dan driver aktual |
| `screenshots/` | Bukti tahap penting, pembatalan, hasil perubahan dan kegagalan |
| `source/`, `console/` | Sumber halaman; console browser disimpan jika didukung driver |
| `source.patch`, `source-sha256.json` | Perubahan lokal dan hash file saat pengujian |

SHA commit dicatat bersama perubahan lokal karena kode belum tentu sudah di-commit. Screenshot memakai data fixture. Rekaman video tidak menjadi artefak runner ini; alur dapat diamati saat mode browser terlihat dijalankan.

Hasil aktual dibaca dari JUnit, bukan angka yang diisi manual. Log dari run gagal sebelumnya tetap berada di folder run masing-masing; gunakan folder run yang sesuai ketika menyusun bukti akhir. Cakupan laporan ini adalah suite PBI-9 di atas.

## Jika eksekusi gagal

- Periksa `report.md`, `runner-error.txt` jika ada, dan log suite terkait.
- Masalah database: periksa layanan MySQL, kredensial `.env.dusk`, nama database uji, dan hak membuat database/trigger.
- Port 8019 terpakai: hentikan server uji lama pada port itu. Server aplikasi biasa port 8000 tidak perlu dihentikan.
- Selenium belum siap: periksa Java pada PATH, akses unduhan resmi GitHub/driver, serta `selenium.log`. Pengunduhan pertama dapat memerlukan waktu lebih lama.
- Konfigurasi Laravel dicache: jalankan `php artisan config:clear`.
- Asset belum dibangun: jalankan `npm.cmd run build`.
- `.env.dusk` dibuat manual: isi APP_KEY dan kredensial lokal yang benar; template `.env.dusk.example` bukan environment siap pakai karena APP_KEY kosong.

Referensi: [Laravel Dusk](https://laravel.com/docs/13.x/dusk), [Selenium Grid Standalone](https://www.selenium.dev/documentation/grid/getting_started/).

## Check otomatis pada pull request ke main

Workflow `.github/workflows/pbi9-tests.yml` menambahkan check **Laravel Dusk + Selenium (PBI-9)** pada pull request yang menargetkan `main`. Workflow juga berjalan pada push ke `main` dan dapat dijalankan manual melalui tab Actions setelah tersedia di branch default.

CI menggunakan Ubuntu, PHP 8.3, Node 24, Java 21, MySQL 8.4, serta Chrome/ChromeDriver. Perintahnya:

```text
php scripts/test-pbi9.php --headless --browser=chrome
```

Workflow menyiapkan environment dan database uji, membangun frontend, lalu menjalankan kedua suite PBI-9. Kredensial MySQL dalam workflow hanya untuk service database sementara milik job CI. Environment/kredensial lokal tidak diunggah.

Setelah perubahan workflow dan runner di-commit serta di-push ke branch pull request, buka tab **Checks** pada PR. Konflik merge perlu diselesaikan sebelum workflow `pull_request` dapat berjalan. Hasil lengkap tersedia pada halaman run Actions, bagian **Summary** dan **Artifacts**. Artifact `pbi9-evidence-...` berisi laporan, JUnit XML, screenshot, serta log dan disimpan selama 14 hari.

Agar check wajib lulus sebelum merge, pengelola repository dapat membuka **Settings ? Rules ? Rulesets** (atau **Settings ? Branches ? Branch protection rules**), memilih target `main`, lalu mengaktifkan **Require status checks to pass** dan memilih check ini setelah pernah berjalan. Workflow ini tidak mengubah aturan repository secara otomatis.

Validasi workflow secara lokal dan pengujian lokal tidak menggantikan hasil GitHub Actions. Status hijau pada PR baru dapat dipastikan setelah run pada GitHub selesai.
