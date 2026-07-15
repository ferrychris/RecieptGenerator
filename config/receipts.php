<?php

return [
    // Path to a system Chrome/Chromium binary used by Browsershot. On
    // Laravel Cloud this is provided by the `chromium` Nixpacks package
    // (see nixpacks.toml) and should be set via CHROME_PATH in the Cloud
    // environment, e.g. /usr/bin/chromium.
    'chrome_path' => env('CHROME_PATH'),

    // Disk used to store generated receipt/invoice PDFs. Defaults to the
    // local disk for development; set to 's3' in production so PDFs are
    // readable regardless of which container/instance handles a request.
    'storage_disk' => env('RECEIPT_STORAGE_DISK', 'local'),

    // Disk used to store publicly-served uploads (business logos). Defaults
    // to the local 'public' disk for development; set to 's3' in production
    // for the same reason as above.
    'uploads_disk' => env('UPLOADS_DISK', 'public'),
];
