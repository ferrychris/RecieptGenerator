<?php

namespace App\Services\Rendering;

use Spatie\Browsershot\Browsershot;

class BrowsershotRenderer implements ReceiptRenderer
{
    public function render(string $html, array $meta = []): string
    {
        $tempPath = storage_path('app/browsershot-tmp');
        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        // On Windows, sys_get_temp_dir() can resolve to a non-writable system
        // directory (e.g. C:\WINDOWS) for processes spawned without an
        // inherited TMP/TEMP env var (such as `php artisan serve` workers).
        // Symfony Process (used internally by Browsershot to shell out to
        // Node) relies on sys_get_temp_dir() for its own pipe lock files, so
        // this must be corrected regardless of setCustomTempPath() above.
        putenv("TMP={$tempPath}");
        putenv("TEMP={$tempPath}");

        $userDataDir = storage_path('app/browsershot-chrome-profile');
        if (! is_dir($userDataDir)) {
            mkdir($userDataDir, 0755, true);
        }

        $browsershot = Browsershot::html($html)
            ->setNodeModulePath(base_path('node_modules'))
            ->setCustomTempPath($tempPath)
            ->userDataDir($userDataDir)
            ->newHeadless()
            ->noSandbox()
            ->showBackground()
            ->margins(0, 0, 0, 0);

        if ($chromePath = config('receipts.chrome_path')) {
            $browsershot->setChromePath($chromePath);
        }

        $page = $meta['page'] ?? 'A4';

        if ($page === '80mm') {
            // Continuous thermal roll: fixed width, generous height since
            // Chrome's PDF export needs a concrete page size (no true
            // infinite-scroll PDF export exists yet).
            $browsershot->paperSize(80, 250, 'mm');
        } else {
            $browsershot->format($page);
        }

        return $browsershot->pdf();
    }
}
