# Aturan yang wajib dipasang saat mengembangkan backend

Schema sudah menyiapkan tempat penyimpanan. Aturan berikut adalah kontrak implementasi untuk tim; selain dua service contoh, belum menjadi fitur berjalan.

## Akun dan hak akses

Gunakan RegistrationService setelah validasi FormRequest. NIK harus aktif, belum dipakai, departemen/perusahaan aktif; password di-hash. Username/email unique. Public registration selalu employee dan departemen berasal dari registry. Role, poin, verified_by, timestamps, dan assigned_admin_id tidak boleh diambil dari request pengguna. Tidak ada mass-assignment terbuka pada model domain.

Autentikasi JWT sesuai proposal masih perlu dibuat, termasuk expiration, logout/revocation dan middleware akun aktif/nondeleted. Jika tim memilih Sanctum, sepakati perubahan dari proposal terlebih dahulu. Web/mobile keduanya mengakses endpoint JSON; pilihan frontend tidak mengubah schema utama. Endpoint, email reset, rate limits dan middleware bukan bagian starter ini.

## Hazard dan penanganan bersamaan

Alur acuan: pending_verification â†’ open/rejected; open â†’ in_progress saat bukti perbaikan dikirim; in_progress â†’ closed setelah diterima atau kembali open setelah ditolak. Admin harus menetapkan repair_pic_id ketika menerima hazard. Semua transisi perlu policy role, alasan, bukti dan validasi status sebelumnya.

Pada transaksi yang sama: lockForUpdate baris hazard, panggil HazardClaimService untuk pemilik admin, cek status, simpan perubahan/pengajuan/foto metadata, status history, audit dan ledger SAP yang berlaku. Audit gagal berarti seluruh perubahan rollback. Kirim notifikasi setelah commit; kegagalan notifikasi dicatat/dicoba ulang tanpa membatalkan laporan yang sudah tersimpan. Dua admin tidak boleh memproses laporan bersamaan. Jangan hanya menyembunyikan tombol di UI.

Karyawan hanya boleh menangani risiko low; admin menangani seluruh tingkat risiko. Sesuai asumsi mockup, penyelesaian sendiri diperbolehkan untuk low, tetapi aturan ini perlu ditinjau bersama tim. Saat menambahkan pending resolution, lock baris hazard dan pastikan belum ada pending lain. Generated unique slot admin tidak mengunci workflow resolution; service wajib melakukannya. Semua layanan perubahan harus menerapkan aturan claim; contoh service belum otomatis dipanggil controller.

Semua penghapusan hazard/users adalah soft delete dengan alasan dan pelaku. Jangan hard delete laporan yang memiliki audit atau poin. Admin biasa tidak boleh mengubah Admin Utama.

## Inspeksi dan Safety Talk

Validasi area pertanyaan sama dengan area inspeksi. Jawaban no wajib catatan dan minimal satu foto; finding dan PIC ditindaklanjuti. Pertanyaan dan departemen disalin sebagai snapshot. Menyelesaikan inspeksi perlu memastikan seluruh finding selesai. Hanya HSE mendapat target wajib, walaupun non-HSE boleh berpartisipasi.

Safety Talk dipublikasikan admin. reads unique(talk,user), upsert agar pembacaan ulang tidak menambah pembaca unik. attendees adalah kehadiran kegiatan, berbeda dari status membaca pengumuman. View count bukan bukti attendance. Publish/verify dan poin harus mengikuti aturan produk yang disepakati.

## SAP dan reward

Default master seeder tidak menentukan bobot poin atau target bisnis. Demo hanya mencontohkan satu poin per hazard terverifikasi. Validasi activity terhadap daftar yang diizinkan; source_id harus merujuk kegiatan yang sesuai dan milik user yang berhak. period dihitung dari tanggal aktivitas di Asia/Jakarta. Tolak effective range overlap, negative targets dan period di tengah bulan.

Award memakai transaction dan unique ledger agar retry tidak menggandakan poin. Rekap dihitung dari ledger valid serta kegiatan valid, bukan counter request. Jika kegiatan dibatalkan/diarsipkan, kebijakan void/reversal perlu disepakati; simpan alasannya. Untuk redemption, lock pengguna atau baris saldo yang sama pada seluruh permintaan paralel; hitung poin valid dikurangi penukaran yang sudah approved/delivered sebelum menyetujui. Jangan menjumlahkan setiap request sebagai saldo baru. Perubahan departemen perlu cutoff/snapshot bulanan agar target historis tetap benar.

## File dan sinkronisasi

Bukti jpg/png, validasi MIME aktual, ukuran dan jumlah yang dibatasi. Nama file dibuat server. Metadata tidak menyertakan absolute path server. Bersihkan file upload jika DB rollback; file storage tidak ikut transaksi MySQL. Audit jangan menyimpan password, token login atau token reset. Policy unduh file harus membaca akses laporan pemiliknya.

Untuk client_request_id: validasi UUID, unique per transaksi, cek user pemilik dan payload sama pada retry. Request yang UUID-nya sama tetapi payload/pemilik berbeda harus ditolak. Draft offline di IndexedDB, upload ulang, conflict resolution dan service worker dikerjakan tim frontend/backend.

## Urutan implementasi yang disarankan

1. Finalisasi keputusan DESIGN.md dan library autentikasi.
2. Auth + policy + master registry/perusahaan/departemen/area/shift/checklist.
3. Hazard create/verify/claim/resolution + foto privat + audit transaksi.
4. Inspection/findings, Safety Talk/reads.
5. SAP rules/ledger/rekap/reward dengan tes duplikasi dan konkurensi.
6. Notifikasi, worker/scheduler, export Excel dan endpoint dashboard.
7. Integrasi mobile/desktop melalui API staging yang sama.
