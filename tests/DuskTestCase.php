<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

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

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        // Default: tampilkan jendela browser Chrome (non-headless) agar efek slow-mo terlihat jelas.
        // Aktifkan DUSK_HEADLESS=true di .env jika ingin berjalan tanpa membuka jendela browser.
        $runHeadless = filter_var($_ENV['DUSK_HEADLESS'] ?? env('DUSK_HEADLESS', false), FILTER_VALIDATE_BOOL);

        $options = (new ChromeOptions)->addArguments(collect([
            '--start-maximized',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--disable-gpu',
            '--no-sandbox',
        ])->when($runHeadless, function (Collection $items) {
            return $items->merge([
                '--headless=new',
                '--window-size=1920,1080',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
