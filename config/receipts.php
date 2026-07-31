<?php

return [
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

    // Disk used to store generated receipt/invoice PDFs. Defaults to the
    // local disk for development; set to 's3' in production so PDFs are
    // readable regardless of which container/instance handles a request.
    'storage_disk' => env('RECEIPT_STORAGE_DISK', 'local'),

    // Disk used to store publicly-served uploads (business logos). Defaults
    // to the local 'public' disk for development; set to 's3' in production
    // for the same reason as above.
    'uploads_disk' => env('UPLOADS_DISK', 'public'),
];
