<?php

namespace Tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use RuntimeException;

abstract class DuskTestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->loadEnvironmentFrom('.env.dusk');
        $app->make(Kernel::class)->bootstrap();

        if (! $app->environment('testing') || $app['config']->get('database.default') !== 'mysql'
            || $app['config']->get('database.connections.mysql.database') !== 'safetrack_dusk') {
            throw new RuntimeException('Dusk hanya boleh memakai MySQL safetrack_dusk dengan APP_ENV=testing. Jalankan php scripts/test-pbi9.php.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $directory = base_path(config('dusk.artifacts'));
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
        $arguments = ['--window-size=1440,1000', '--disable-search-engine-choice-screen', '--disable-smooth-scrolling'];
        if (config('dusk.headless')) {
            $arguments[] = '--headless=new';
        }
        $edge = config('dusk.browser') === 'edge';
        $capabilities = $edge ? DesiredCapabilities::microsoftEdge() : DesiredCapabilities::chrome();
        $browserOptions = ['args' => $arguments];
        if ($binary = config('dusk.browser_binary')) {
            $browserOptions['binary'] = $binary;
        }
        $capabilities->setCapability($edge ? 'ms:edgeOptions' : 'goog:chromeOptions', $browserOptions);
        $capabilities->setCapability('pageLoadStrategy', 'eager');

        $driver = RemoteWebDriver::create(config('dusk.driver_url'), $capabilities, 30000, 60000);
        file_put_contents(base_path(config('dusk.artifacts')).'/browser-capabilities.json', json_encode($driver->getCapabilities()->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $driver;
    }

    protected function evidence(Browser $browser, string $step = 'result'): void
    {
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $this->nameWithDataSet()).'-'.$step;
        $browser->screenshot($filename)->storeSource($filename);
    }
}
