<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class SafeTrackSeeder extends Seeder {
 public function run(): void { $this->call(MasterDataSeeder::class); }
}
