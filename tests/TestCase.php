<?php
namespace Tests;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
abstract class TestCase extends BaseTestCase {
 public function createApplication() {
  $app=parent::createApplication();
  // This guard runs before RefreshDatabase can execute migrate:fresh.
  $database=$app['config']->get('database.connections.mysql.database');
  if(!$app->environment('testing') || $app['config']->get('database.default')!=='mysql' || $database!=='safetrack_test'){
   throw new \RuntimeException('Tes hanya boleh memakai MySQL database safetrack_test; jalankan config:clear dan cek .env.testing.');
  }
  return $app;
 }
}
