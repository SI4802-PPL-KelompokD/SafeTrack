<?php

return [
    'driver_url' => env('DUSK_DRIVER_URL', 'http://127.0.0.1:4445'),
    'browser' => env('DUSK_BROWSER', 'edge'),
    'browser_binary' => env('DUSK_BROWSER_BINARY'),
    'headless' => env('DUSK_HEADLESS', false),
    'artifacts' => env('PBI9_ARTIFACTS', 'storage/app/pbi9-evidence/manual'),
];
