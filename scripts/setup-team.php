<?php
// Non-destructive, cross-platform bootstrap; no database passwords are embedded here.
$root=dirname(__DIR__);chdir($root);
function stopSetup(string $message): never {fwrite(STDERR,$message.PHP_EOL);exit(1);}
if(version_compare(PHP_VERSION,'8.3.0','<')){stopSetup('Butuh PHP >=8.3. Cek php -v dan PHP CLI Laragon.');}
foreach(['pdo_mysql','mbstring','openssl','fileinfo','xml'] as $ext){if(!extension_loaded($ext)){stopSetup('Aktifkan ekstensi PHP: '.$ext);}}
if(!file_exists('vendor/autoload.php')){stopSetup('Jalankan composer install dahulu.');}
if(!file_exists('.env')){
 if(!copy('.env.example','.env')){stopSetup('Gagal membuat .env');}
 echo '.env dibuat. Buat database safetrack dan isi DB_* sesuai MySQL lokal, lalu jalankan composer setup:team lagi.'.PHP_EOL;exit(0);
}
require 'vendor/autoload.php';
$values=Dotenv\Dotenv::parse(file_get_contents('.env'));
if(($values['DB_CONNECTION']??'')!=='mysql'){stopSetup('Set DB_CONNECTION=mysql pada .env dahulu.');}
if(empty($values['DB_DATABASE'])){stopSetup('Isi DB_DATABASE di .env.');}
if(!empty($values['DB_URL'])){stopSetup('Kosongkan DB_URL untuk setup lokal yang memakai DB_*.');}
function artisan(array $arguments): void {
 $command=escapeshellarg(PHP_BINARY).' '.escapeshellarg('artisan');
 foreach($arguments as $argument){$command.=' '.escapeshellarg($argument);}
 passthru($command,$status);if($status!==0){stopSetup('Perintah gagal. Periksa pesan di atas; data tidak dihapus otomatis.');}
}
artisan(['config:clear']);
if(empty($values['APP_KEY'])){artisan(['key:generate']);}
artisan(['migrate']);
artisan(['db:seed']);
artisan(['migrate:status']);
echo 'Schema dan master minimum selesai. Opsional lokal: php artisan db:seed --class=DemoSeeder'.PHP_EOL;
