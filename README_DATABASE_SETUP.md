# Setup database SafeTrack

Gunakan panduan README.md pada root repo untuk setup tim, clone pertama, pull berikutnya dan database tes. Instruksi lama pemasangan addon sudah digantikan oleh panduan tersebut.

DatabaseSeeder sekarang menjalankan SafeTrackSeeder, sehingga php artisan db:seed atau migrate --seed mengisi master minimum tanpa akun Test User. DemoSeeder tetap eksplisit dan opsional untuk local/testing.
