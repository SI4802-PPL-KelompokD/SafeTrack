# SafeTrack

Backend Laravel 13 untuk pelaporan K3, inspeksi, Safety Talk dan SAP. Source awal ini siap dikembangkan per modul setelah setup lokal di bawah selesai. Frontend, autentikasi/API dan otomatisasi bisnis belum selesai.

## Persyaratan tim

- PHP >=8.3 dengan pdo_mysql, mbstring, openssl, fileinfo dan XML. Pastikan php -v pada terminal menggunakan versi yang sesuai.
- Composer 2, Git, MySQL >=8.0.16 (CI memakai 8.4). Jangan gunakan SQLite untuk migration SafeTrack saat ini.
- Untuk aset frontend: Node ^20.19.0 atau >=22.12.0 sesuai lock Vite, serta npm. Node tidak dibutuhkan untuk membentuk tabel database.

Commit composer.lock dan package-lock.json agar versi dependency sama. Jangan commit .env, vendor, node_modules, file upload, dump database atau log. .env.example adalah template aman, APP_KEY sengaja kosong.

## Setelah clone pertama

Jalankan dari root repository hasil clone, bukan path komputer anggota lain:

```bash
composer install
```

Salin .env.example menjadi .env jika Composer belum membuatnya. Jalankan MySQL Laragon dan buat database safetrack:

```sql
CREATE DATABASE safetrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Bila sudah ada, periksa isinya dan gunakan database lokal yang benar; jangan drop database. Edit DB_HOST/PORT/DATABASE/USERNAME/PASSWORD pada .env sesuai MySQL masing-masing. Template memakai root tanpa password sebagai contoh Laragon lokal; jangan menganggap ini benar untuk setiap anggota. Pertahankan APP_KEY yang sudah terisi.

```bash
composer setup:team
```

Script memeriksa PHP dan konfigurasi, membuat APP_KEY hanya jika kosong, menjalankan migrate biasa dan mengisi master minimum. Jika .env belum ada script hanya membuat template dan meminta kamu mengisinya. Tidak ada perintah menghapus tabel. Untuk data contoh lokal opsional:

```bash
php artisan db:seed --class=DemoSeeder
```

Demo membuat perusahaan/departemen, shift/area/checklist, satu aturan poin contoh dan 3 akun: admin, hse, karyawan (email username@safetrack.test). Password demo: DemoOnly!2026. Ini belum merupakan endpoint login. Demo hanya local/testing. Untuk awal tanpa demo, jalankan php artisan safetrack:admin. Jangan menjalankan kedua jalur pada database yang sudah berisi Admin Utama nyata.

```bash
php artisan migrate:status
php artisan serve
```

Bila membutuhkan aset frontend:

```bash
npm ci
npm run dev
```

Pada PowerShell yang memblokir npm.ps1, gunakan npm.cmd ci dan npm.cmd run dev. Untuk hasil build gunakan npm run build.

## Setelah pull berikutnya

```bash
composer install
php artisan config:clear
php artisan migrate
php artisan db:seed
```

Jalankan npm ci jika package-lock.json berubah. DatabaseSeeder sekarang hanya memanggil SafeTrackSeeder: aman diulang dan tidak membuat Test User. Data bisnis atau demo tidak direset oleh db:seed. Ketika file .env baru mendapat variabel, maintainer mencatatnya di PR; anggota menyalin variabel baru dari .env.example ke .env sendiri.

## Aturan kerja database tim

- Tiap anggota memakai database lokal sendiri. Git membagikan schema melalui migration dan data awal melalui seeder, bukan isi database kerja milik satu orang.
- Buat migration BARU untuk setiap perubahan schema; jangan edit migration lama yang sudah dipakai tim. Gunakan nama timestamp yang unik dan koordinasikan perubahan tabel/kolom di PR.
- Jangan menjalankan migrate:fresh atau rollback pada database anggota lain atau server bersama. Migration tambahan NIK akan berhenti bila users lama belum dipetakan; jangan hapus akun sebagai jalan pintas.
- Master minimum tidak berarti semua aturan bisnis SAP sudah aktif. Bobot, target dan aturan reward perlu ditetapkan tim.
- Saat integrasi mobile/desktop, keduanya memakai API backend staging yang sama dengan MySQL privat. Kredensial DB tidak dimasukkan ke frontend.

## Tes database pada database TERPISAH

Buat database safetrack_test dan beri hak akun MySQL tes hanya ke database itu. Tes akan menghapus/membuat tabel pada safetrack_test. Jangan memakai akun produksi.

```sql
CREATE DATABASE safetrack_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Salin .env.testing.example menjadi .env.testing, isi kredensial host/port/user/password tes. phpunit.xml memaksa MySQL dan nama safetrack_test; guard TestCase menolak konfigurasi lain sebelum RefreshDatabase berjalan. Lalu:

```bash
php artisan key:generate --env=testing
php artisan config:clear
php vendor/bin/phpunit
```

Workflow .github/workflows/database.yml menjalankan instalasi bersih dependency PHP, migration, seed dua kali, rollback/reapply dan tes integrasi pada MySQL 8.4. Belum dijalankan di GitHub saat audit ini dibuat; cek status hijau sesudah push.

## Dokumen

- docs/safetrack/AUDIT_REPORT.md: temuan, perubahan dan hasil pemeriksaan.
- docs/safetrack/DESIGN.md: asumsi proposal dan keputusan sementara.
- docs/safetrack/ERD.md dan DATA_DICTIONARY.md: rancangan database.
- docs/safetrack/BACKEND_RULES.md: aturan yang perlu diterapkan pada layanan/API.

Build tidak lagi mengambil font dari layanan luar; gunakan font sistem/fallback sampai frontend menetapkan aset font lokal. Vite dan dependency lock tidak berubah.
