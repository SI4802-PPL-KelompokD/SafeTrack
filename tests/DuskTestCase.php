<?php

namespace Tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use RuntimeException;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        if (file_exists(base_path('.env.dusk'))) {
            $app->loadEnvironmentFrom('.env.dusk');
        }
        $app->make(Kernel::class)->bootstrap();

        if (file_exists(base_path('.env.dusk')) && (
            ! $app->environment('testing')
            || $app['config']->get('database.default') !== 'mysql'
            || $app['config']->get('database.connections.mysql.database') !== 'safetrack_dusk'
        )) {
            throw new RuntimeException('Dusk hanya boleh memakai MySQL safetrack_dusk dengan APP_ENV=testing. Jalankan php scripts/test-pbi9.php.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $artifactsPath = config('dusk.artifacts', 'storage/app/dusk-artifacts');
        $directory = base_path($artifactsPath);
        foreach (['screenshots', 'console', 'source'] as $type) {
            if (! is_dir($directory.'/'.$type)) {
                mkdir($directory.'/'.$type, 0755, true);
            }
        }
        Browser::$storeScreenshotsAt = $directory.'/screenshots';
        Browser::$storeConsoleLogAt = $directory.'/console';
        Browser::$storeSourceAt = $directory.'/source';
        Browser::$waitSeconds = 10;
    }

    protected function tearDown(): void
    {
        static::closeAll();
        parent::tearDown();
    }

    protected function driver(): RemoteWebDriver
    {
        $arguments = [
            '--start-maximized',
            '--window-size=1440,1000',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--disable-gpu',
            '--no-sandbox',
        ];

        $runHeadless = config('dusk.headless', filter_var($_ENV['DUSK_HEADLESS'] ?? env('DUSK_HEADLESS', false), FILTER_VALIDATE_BOOL));
        if ($runHeadless) {
            $arguments[] = '--headless=new';
        }

        $edge = config('dusk.browser') === 'edge';
        $capabilities = $edge ? DesiredCapabilities::microsoftEdge() : DesiredCapabilities::chrome();
        $capabilities->setCapability($edge ? 'ms:edgeOptions' : 'goog:chromeOptions', ['args' => $arguments]);
        $capabilities->setCapability('pageLoadStrategy', 'eager');

        $driverUrl = config('dusk.driver_url') ?? $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL', 'http://localhost:9515');
        $driver = RemoteWebDriver::create($driverUrl, $capabilities, 30000, 60000);

        $artifactsPath = config('dusk.artifacts');
        if ($artifactsPath) {
            $dir = base_path($artifactsPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(
                $dir.'/browser-capabilities.json',
                json_encode($driver->getCapabilities()->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        return $driver;
    }

    protected function evidence(Browser $browser, string $step = 'result'): void
    {
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $this->nameWithDataSet()).'-'.$step;
        $browser->screenshot($filename)->storeSource($filename);
    }
}