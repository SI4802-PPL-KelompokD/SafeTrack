<?php

use Dotenv\Dotenv;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
chdir($root);
$options = getopt('', ['headless', 'browser:', 'filter:', 'browser-only', 'feature-only']);
$runId = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('Ymd-His');
$relativeArtifacts = 'storage/app/pbi9-evidence/'.$runId;
$artifacts = $root.'/'.$relativeArtifacts;
mkdir($artifacts, 0755, true);
$services = [];
$commands = [];
$exitCode = 0;

function commandOutput(array $command, string $root): string
{
    $process = new Process($command, $root);
    $process->setTimeout(60)->run();
    if (! $process->isSuccessful()) {
        throw new RuntimeException($process->getErrorOutput().$process->getOutput());
    }

    return trim($process->getOutput());
}

function ready(string $url): bool
{
    $context = stream_context_create(['http' => ['timeout' => 1, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $context);

    return $body !== false && ($url === 'http://127.0.0.1:4445/status'
        ? (json_decode($body, true)['value']['ready'] ?? false) : true);
}

function waitForService(string $url, Process $process, int $seconds): void
{
    $deadline = time() + $seconds;
    while (time() < $deadline) {
        if (ready($url)) {
            return;
        }
        if (! $process->isRunning()) {
            throw new RuntimeException('Service berhenti sebelum siap: '.$url);
        }
        usleep(250000);
    }
    throw new RuntimeException('Service belum siap setelah '.$seconds.' detik: '.$url);
}

function startService(array $command, string $root, string $log, array $environment = []): Process
{
    $process = new Process($command, $root, $environment);
    $process->setTimeout(null);
    $process->start(static function (string $type, string $buffer) use ($log): void {
        file_put_contents($log, $buffer, FILE_APPEND);
    });

    return $process;
}

function runTests(array $command, string $root, string $log, array $environment, array $services = []): int
{
    echo PHP_EOL.'> '.implode(' ', $command).PHP_EOL;
    $process = new Process($command, $root, $environment);
    $process->setTimeout(1800);

    $process->start(static function (string $type, string $buffer) use ($log): void {
        echo $buffer;
        file_put_contents($log, $buffer, FILE_APPEND);
    });
    while ($process->isRunning()) {
        foreach ($services as $service) {
            $service->getIncrementalOutput();
            $service->getIncrementalErrorOutput();
        }
        $process->checkTimeout();
        usleep(100000);
    }

    return $process->getExitCode() === 0 ? 0 : 1;
}

try {
    if (file_exists('bootstrap/cache/config.php')) {
        throw new RuntimeException('Jalankan php artisan config:clear sebelum pengujian.');
    }
    if (! in_array($options['browser'] ?? 'edge', ['edge', 'chrome'], true)) {
        throw new RuntimeException('Pilihan browser yang didukung: edge atau chrome.');
    }
    $source = Dotenv::parse(file_get_contents('.env'));
    if (($source['DB_CONNECTION'] ?? 'mysql') !== 'mysql') {
        throw new RuntimeException('Runner ini memerlukan MySQL lokal.');
    }
    if (! file_exists('.env.dusk')) {
        $environment = [
            'APP_NAME' => 'SafeTrack PBI9 Test', 'APP_ENV' => 'testing',
            'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)), 'APP_DEBUG' => 'true',
            'APP_URL' => 'http://127.0.0.1:8019', 'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $source['DB_HOST'] ?? '127.0.0.1', 'DB_PORT' => $source['DB_PORT'] ?? '3306',
            'DB_DATABASE' => 'safetrack_dusk', 'DB_USERNAME' => $source['DB_USERNAME'] ?? 'root',
            'DB_PASSWORD' => $source['DB_PASSWORD'] ?? '', 'DB_URL' => '',
            'SESSION_DRIVER' => 'database', 'SESSION_COOKIE' => 'safetrack_dusk_session',
            'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'BCRYPT_ROUNDS' => '4',
        ];
        $lines = [];
        foreach ($environment as $key => $value) {
            $lines[] = $key.'="'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
        }
        file_put_contents('.env.dusk', implode(PHP_EOL, $lines).PHP_EOL);
    }
    $dusk = Dotenv::parse(file_get_contents('.env.dusk'));
    if (($dusk['DB_DATABASE'] ?? '') !== 'safetrack_dusk' || ($dusk['APP_ENV'] ?? '') !== 'testing'
        || ($dusk['APP_URL'] ?? '') !== 'http://127.0.0.1:8019' || ! empty($dusk['DB_URL'])) {
        throw new RuntimeException('.env.dusk harus memakai APP_ENV=testing, safetrack_dusk, APP_URL=http://127.0.0.1:8019 dan DB_URL kosong.');
    }
    $pdo = new PDO('mysql:host='.$dusk['DB_HOST'].';port='.$dusk['DB_PORT'], $dusk['DB_USERNAME'], $dusk['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS safetrack_dusk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('CREATE DATABASE IF NOT EXISTS safetrack_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo = null;

    $metadata = [
        'started_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format(DATE_ATOM), 'timezone' => 'Asia/Jakarta', 'php' => PHP_VERSION,
        'commit' => commandOutput(['git', 'rev-parse', 'HEAD'], $root),
        'working_tree' => commandOutput(['git', 'status', '--short'], $root),
        'browser' => $options['browser'] ?? 'edge', 'headless' => isset($options['headless']),
        'filter' => $options['filter'] ?? null,
    ];
    file_put_contents($artifacts.'/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    file_put_contents($artifacts.'/source.patch', commandOutput(['git', 'diff', '--binary'], $root));
    $hashes = [];
    foreach (explode("\n", commandOutput(['git', 'ls-files', '-m', '-o', '--exclude-standard'], $root)) as $file) {
        if ($file !== '' && is_file($file)) {
            $hashes[$file] = hash_file('sha256', $file);
        }
    }
    file_put_contents($artifacts.'/source-sha256.json', json_encode($hashes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $childEnvironment = [
        'PBI9_ARTIFACTS' => $relativeArtifacts, 'DUSK_BROWSER' => $metadata['browser'],
        'DUSK_HEADLESS' => $metadata['headless'] ? 'true' : 'false',
    ];
    $filter = isset($options['filter']) ? ['--filter', $options['filter']] : [];

    if (! isset($options['browser-only'])) {
        $command = array_merge([PHP_BINARY, 'vendor/bin/phpunit', '-c', 'phpunit.xml', 'tests/Feature/Pbi9EmployeeManagementTest.php', '--log-junit', $artifacts.'/feature.xml'], $filter);
        $commands[] = implode(' ', $command);
        $exitCode = max($exitCode, runTests($command, $root, $artifacts.'/feature.log', $childEnvironment));
    }
    if (! isset($options['feature-only'])) {
        if (ready('http://127.0.0.1:8019/up')) {
            throw new RuntimeException('Port 8019 sedang dipakai. Hentikan hanya server uji pada port ini lalu ulangi.');
        }
        if (! ready('http://127.0.0.1:4445/status')) {
            $jar = 'storage/app/selenium/selenium-server-4.52.0.jar';
            if (! is_file($jar)) {
                if (! is_dir(dirname($jar))) {
                    mkdir(dirname($jar), 0755, true);
                }
                echo "Mengunduh Selenium 4.52.0 dari rilis resmi...\n";
                commandOutput(['curl.exe', '-fL', '--retry', '2', '-o', $jar, 'https://github.com/SeleniumHQ/selenium/releases/download/selenium-4.52.0/selenium-server-4.52.0.jar'], $root);
            }
            $selenium = startService(['java', '-jar', $jar, 'standalone', '--host', '127.0.0.1', '--port', '4445', '--selenium-manager', 'true', '-I', $metadata['browser'], '--max-sessions', '2', '--tracing', 'false'], $root, $artifacts.'/selenium.log', ['SE_AVOID_STATS' => 'true']);
            $services[] = $selenium;
            waitForService('http://127.0.0.1:4445/status', $selenium, 180);
        } else {
            file_put_contents($artifacts.'/selenium.log', "Menggunakan Selenium yang sudah aktif pada 127.0.0.1:4445.\n");
        }
        $server = startService([PHP_BINARY, 'artisan', 'serve', '--env=dusk', '--host=127.0.0.1', '--port=8019', '--no-reload', '--no-interaction'], $root, $artifacts.'/application.log');
        $services[] = $server;
        waitForService('http://127.0.0.1:8019/up', $server, 30);
        $command = array_merge([PHP_BINARY, 'vendor/bin/phpunit', '-c', 'phpunit.dusk.xml', '--log-junit', $artifacts.'/browser.xml'], $filter);
        $commands[] = implode(' ', $command);
        $exitCode = max($exitCode, runTests($command, $root, $artifacts.'/browser.log', $childEnvironment, $services));
    }
} catch (Throwable $exception) {
    $exitCode = 1;
    file_put_contents($artifacts.'/runner-error.txt', $exception->getMessage());
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
} finally {
    foreach (array_reverse($services) as $service) {
        $service->stop(3);
    }
    $totals = ['passed' => 0, 'failed' => 0, 'skipped' => 0];
    $caseRows = [];
    foreach (['feature', 'browser'] as $suite) {
        if (! is_file($artifacts.'/'.$suite.'.xml')) {
            continue;
        }
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($artifacts.'/'.$suite.'.xml');
        if ($xml === false) {
            $exitCode = 1;

            continue;
        }
        foreach ($xml->xpath('//testcase') as $test) {
            $status = isset($test->failure) || isset($test->error) ? 'failed' : (isset($test->skipped) ? 'skipped' : 'passed');
            $totals[$status]++;
            $caseRows[] = '| '.$suite.' | '.str_replace('|', '/', (string) $test['name']).' | '.$status.' | '.(string) $test['time'].' |';
        }
    }
    $sha = $metadata['commit'] ?? 'Tidak tersedia';
    $report = "# Hasil pengujian PBI-9\n\n| Informasi | Hasil |\n|---|---|\n"
        .'| Status eksekusi | '.($exitCode === 0 ? 'Selesai' : 'Gagal / perlu diperiksa')." |\n"
        .'| Jumlah passed | '.$totals['passed']." |\n| Jumlah failed | ".$totals['failed']." |\n| Jumlah skipped/pending | ".$totals['skipped']." |\n"
        .'| SHA commit yang diuji | `'.$sha."` + perubahan lokal pada source.patch/source-sha256.json |\n"
        ."| Bukti | Screenshot, source halaman, log, metadata dan JUnit XML dalam folder ini |\n\n"
        ."## Perintah\n\n```text\n".implode("\n", $commands)."\n```\n\n"
        ."## Kasus\n\n| Suite | Kasus | Hasil | Detik |\n|---|---|---|---|\n".implode("\n", $caseRows)."\n";
    if (isset($metadata)) {
        $metadata['finished_at'] = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format(DATE_ATOM);
        $metadata['counts'] = $totals;
        $metadata['exit_code'] = $exitCode;
        file_put_contents($artifacts.'/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    file_put_contents($artifacts.'/report.md', $report);
    $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $gallery = '';
    foreach (glob($artifacts.'/screenshots/*.png') ?: [] as $screenshot) {
        $name = basename($screenshot);
        $gallery .= '<details><summary>'.$escape($name).'</summary><a href="screenshots/'.rawurlencode($name).'"><img loading="lazy" src="screenshots/'.rawurlencode($name).'" alt="'.$escape($name).'"></a></details>';
    }
    $html = '<!doctype html><html lang="id"><meta charset="utf-8"><title>Hasil PBI-9</title><style>body{font:16px system-ui;max-width:1200px;margin:32px auto;padding:0 20px;color:#102b46}table{border-collapse:collapse;width:100%}td,th{border:1px solid #ccd5df;padding:10px;text-align:left}details{margin:12px 0;border:1px solid #ccd5df;padding:12px}summary{cursor:pointer;overflow-wrap:anywhere}img{max-width:100%;height:auto}code,pre{overflow-wrap:anywhere;white-space:pre-wrap}</style><h1>Hasil pengujian PBI-9</h1><table><tr><th>Informasi</th><th>Hasil</th></tr><tr><td>Status eksekusi</td><td>'.($exitCode === 0 ? 'Selesai' : 'Gagal / perlu diperiksa').'</td></tr><tr><td>Passed</td><td>'.$totals['passed'].'</td></tr><tr><td>Failed</td><td>'.$totals['failed'].'</td></tr><tr><td>Skipped/pending</td><td>'.$totals['skipped'].'</td></tr><tr><td>SHA commit</td><td><code>'.$escape($sha).'</code> + perubahan lokal pada source.patch/source-sha256.json</td></tr></table><h2>Perintah pengujian</h2><pre>'.$escape(implode("\n", $commands)).'</pre><p><a href="report.md">Hasil setiap kasus</a> | <a href="metadata.json">Metadata</a> | <a href="browser.xml">JUnit browser</a> | <a href="feature.xml">JUnit feature</a></p><h2>Bukti screenshot</h2>'.$gallery.'</html>';
    file_put_contents($artifacts.'/report.html', $html);
    file_put_contents('storage/app/pbi9-evidence/latest.txt', $relativeArtifacts);
    echo PHP_EOL.'Bukti dan laporan: '.$relativeArtifacts.'/report.md'.PHP_EOL;
}
exit($exitCode);
