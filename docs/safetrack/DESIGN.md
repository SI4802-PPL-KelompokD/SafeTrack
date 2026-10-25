# Keputusan desain dan asumsi proposal

Sumber: Proposal PPL_B-A.docx (3).pdf. Ini rancangan implementasi awal, bukan salinan persis gambar ERD. Migrations adalah sumber schema yang dijalankan. Database MySQL terpusat melayani Laravel; perangkat tidak membuat koneksi MySQL langsung.

## Perbedaan di dalam proposal

| Topik | Bagian awal / use case / class | Mockup hlm.70â€“80 | Acuan sementara paket |
|---|---|---|---|
| Status awal laporan | Open | Menunggu Verifikasi | pending_verification â†’ open setelah diterima; rejected bila ditolak |
| Status akhir | Selesai | Close | closed; label UI boleh Selesai/Close |
| Penyelesai laporan sendiri | UC-10 melarang | Hlm.70 mengizinkan risiko rendah | Diizinkan risiko rendah; config/safetrack.php menandai asumsi, perlu dibaca service penyelesaian yang kelak dibuat |
| Inspeksi non-HSE | Class diagram khusus HSE | Hlm.71 bersifat sukarela | Data menerima semua pengguna; hanya HSE punya target wajib |
| Safety Talk | Kegiatan, peserta, verifikasi | Pengumuman dan jumlah pembaca | safety_talks + reads; attendees/held_at/verified_at disediakan untuk kebutuhan kegiatan |
| Poin | Beberapa aktivitas dan aturan konversi | Hlm.80 satu hazard valid = satu poin | Aturan berversi; demo hanya hazard_verified=1; nilai lain tidak diada-adakan |
| Autentikasi | Bagian arsitektur menyebut JWT | Frontend tim belum diputuskan | Belum memasang library JWT/Sanctum. sessions/reset Laravel tersedia; pilih auth saat implementasi API |
| Admin Utama | Satu admin utama | Admin/HSE | role super_admin/admin/employee, unique generated column menjamin maksimal satu super_admin |

Keputusan ini bisa direvisi sebelum tim mengembangkan fitur. Tidak perlu menunggu pilihan bahasa frontend untuk menjalankan migration.

## Pola penyimpanan

- NIK berupa string (nol di depan dipertahankan) pada employee_registry, unique. users.employee_registry_id juga unique: satu NIK satu akun termasuk akun soft-deleted. Gunakan aktivasi kembali akun, bukan registrasi ulang NIK.
- users.department_id adalah departemen saat ini. Hazard menyimpan reporter_department_id dan daily_inspections.department_id sebagai snapshot saat kegiatan, sehingga perubahan departemen tidak mengubah laporan lama.
- Peran akun berbeda dengan is_hse departemen. Pengguna employee pada departemen HSE tetap HSE; jangan menghitung target hanya dari role admin.
- Data master yang sudah dirujuk transaksi tidak boleh hard delete (FK RESTRICT); nonaktifkan. Hazard dan users memakai soft delete, deleted_by dan deletion_reason.
- Foto disimpan privat di file storage, database hanya metadata/path. Tidak ada BLOB gambar. Jangan menjalankan storage:link untuk membuka bukti privat. Backend harus memeriksa izin sebelum mengirim file.
- client_request_id UUID pada transaksi utama mencegah duplikasi sinkronisasi offline. Retry memakai UUID yang sama; backend harus memeriksa pemilik dan kesamaan payload. Constraint unique saja belum cukup untuk respons idempoten.
- Jawaban inspeksi menyimpan question_snapshot agar perubahan pertanyaan master tidak mengubah bukti historis. Ya/Tidak/NA adalah opsi awal; NA perlu disetujui pemilik produk.
- Timestamp disimpan UTC. inspection_date dan period dihitung dari waktu bisnis Asia/Jakarta. period wajib tanggal pertama bulan; validasi di backend.
- sap_entries menjadi ledger dengan point snapshot dan unique(activity,source_id,user_id). source_id bersifat referensi lintas jenis kegiatan, jadi validasi keberadaan dan kecocokan sumber harus dilakukan service (tidak ada FK polymorphic). Rekap bulanan adalah cache hasil hitung, bukan satu-satunya bukti perolehan.
- sap_targets per pengguna/per bulan/per aktivitas. Target departemen/global jika dibutuhkan dapat diterapkan sebagai template yang diperluas ke user HSE; cakupan global/departemen belum dibuat sebagai fitur.
- Pengubahan aturan SAP hanya berlaku berdasarkan periode efektif yang disepakati. Cegah rentang tumpang tindih. Penghitungan ulang/reversal dan persetujuan reward belum berjalan otomatis.
- Registrasi dan claim admin disertai contoh service transaksi. Cek akses, workflow, audit lengkap, SAP, foto dan notifikasi masih perlu dihubungkan oleh pengembang fitur.

## Versi project

Disesuaikan dengan composer.json project pengguna: laravel/framework ^13.17, PHP ^8.3. Framework terpasang yang dibaca: 13.38.0. Tidak ada composer.json/composer.lock yang diganti oleh addon ini. Migration bawaan project tetap dipakai.
