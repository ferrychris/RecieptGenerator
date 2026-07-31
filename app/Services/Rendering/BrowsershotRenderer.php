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

        $nodeModulePath = config('receipts.node_module_path') ?: base_path('node_modules');

        // Browsershot's browser.cjs requires('puppeteer'), so a missing package
        // fails deep inside a spawned Node process — surfacing as an opaque
        // "Exit Code: 1" with a JS stack trace. Check up front so the error
        // names the actual problem and where it looked.
        if (! is_dir($nodeModulePath.'/puppeteer')) {
            throw new \RuntimeException(
                "The 'puppeteer' npm package was not found at [{$nodeModulePath}/puppeteer]. "
                .'Browsershot cannot render PDFs without it. Install it in the runtime '
                .'environment (and set BROWSERSHOT_NODE_MODULE_PATH if it lives elsewhere).'
            );
        }

        // Must be set on THIS process: Browsershot runs Node via Symfony
        // Process, which inherits the parent environment. Setting it via
        // Browsershot's setEnvironmentOptions() would only reach the Chromium
        // subprocess, which is too late for puppeteer to locate its browser.
        if ($cacheDir = config('receipts.puppeteer_cache_dir')) {
            putenv("PUPPETEER_CACHE_DIR={$cacheDir}");
        }

        $browsershot = Browsershot::html($html)
            ->setNodeModulePath($nodeModulePath)
            ->setCustomTempPath($tempPath)
            ->userDataDir($userDataDir)
            ->newHeadless()
            ->noSandbox()
            ->showBackground()
            ->margins(0, 0, 0, 0);

        $chromePath = config('receipts.chrome_path');

        if (! $chromePath || str_contains($chromePath, 'chrome.exe')) {
            $commonPaths = [
                '/usr/bin/chromium',
                '/usr/bin/chromium-browser',
                '/usr/bin/google-chrome',
            ];
            foreach ($commonPaths as $path) {
                if (file_exists($path)) {
                    $chromePath = $path;
                    break;
                }
            }
        }

        if ($chromePath) {
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
