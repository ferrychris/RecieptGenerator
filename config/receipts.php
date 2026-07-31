<?php

return [
    // Which engine turns receipt HTML into PDF bytes:
    //
    //   'browsershot'  — local headless Chrome via Node + puppeteer. Fast and
    //                    free, but needs the puppeteer npm package AND a
    //                    Chromium binary present at runtime. Good for local
    //                    development.
    //
    //   'browserless'  — POSTs the HTML to a remote headless-Chrome service.
    //                    Needs only outbound HTTP, so it works on hosts that
    //                    don't ship node_modules to the runtime container
    //                    (Laravel Cloud among them, where Browsershot cannot
    //                    work at all). Same Chrome under the hood, so output
    //                    is identical.
    'renderer' => env('RECEIPT_RENDERER', 'browsershot'),

    'browserless' => [
        // e.g. https://production-sfo.browserless.io, or http://chrome:3000
        // for a self-hosted ghcr.io/browserless/chromium container.
        'url' => env('BROWSERLESS_URL'),
        'token' => env('BROWSERLESS_TOKEN'),
        'timeout' => env('BROWSERLESS_TIMEOUT', 60),
    ],

    // Path to a system Chrome/Chromium binary used by Browsershot. On
    // Laravel Cloud this is provided by the `chromium` Nixpacks package
    // (see nixpacks.toml) and should be set via CHROME_PATH in the Cloud
    // environment, e.g. /usr/bin/chromium.
    'chrome_path' => env('CHROME_PATH'),

    // Where the `puppeteer` npm package lives. Browsershot shells out to Node
    // and that script does `require('puppeteer')`, so this package must exist
    // at runtime — a system Chrome binary alone is NOT enough.
    //
    // Many PaaS hosts (Laravel Cloud included) build front-end assets in a
    // separate container and ship only public/build to the runtime container,
    // so the default base_path('node_modules') won't exist there. Install
    // puppeteer during deploy and point this at wherever it landed.
    'node_module_path' => env('BROWSERSHOT_NODE_MODULE_PATH', base_path('node_modules')),

    // Where puppeteer stores the Chromium build it downloads. Its default is
    // ~/.cache/puppeteer, which is often not writable (or not persisted) in a
    // container. Must be set to the SAME value at install time and at runtime,
    // otherwise puppeteer downloads to one place and looks in another.
    'puppeteer_cache_dir' => env('PUPPETEER_CACHE_DIR'),

    // Disk holding generated receipt PDFs. Defaults to the local disk for
    // development; set to 'receipts' in production (see the disk of that
    // name in config/filesystems.php, backed by the receipts bucket) so
    // stored PDFs are readable from any container.
    'storage_disk' => env('RECEIPT_STORAGE_DISK', 'local'),

    // Disk holding uploaded organization logos. Defaults to the local
    // 'public' disk for development; set to 'logos' in production.
    'uploads_disk' => env('UPLOADS_DISK', 'public'),
];
