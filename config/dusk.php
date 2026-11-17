<?php

return [
    'driver_url' => env('DUSK_DRIVER_URL', 'http://127.0.0.1:9515'),
    'browser' => env('DUSK_BROWSER', 'chrome'),
    'headless' => env('DUSK_HEADLESS', false),
    'artifacts' => env('DUSK_ARTIFACTS', 'tests/Browser'),
];
