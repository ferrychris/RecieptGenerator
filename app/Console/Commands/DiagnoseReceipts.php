<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\Rendering\ReceiptRenderer;
use App\ViewModels\InvoiceDocumentBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

/**
 * Diagnoses the receipt PDF pipeline in whatever environment it's run in.
 *
 * Exists because a failure anywhere in this pipeline surfaces to the user as
 * an opaque 500 (production hides exception messages), and the pipeline has
 * several environment-specific dependencies — a Chrome binary, a writable
 * temp dir, and object storage — that are easy to get wrong on a new host.
 * Run it on the server (e.g. Laravel Cloud's command runner) to find out
 * which step is actually broken.
 */
class DiagnoseReceipts extends Command
{
    protected $signature = 'receipts:diagnose {invoice? : Invoice ID to do a full end-to-end render test with}';

    protected $description = 'Check the receipt PDF pipeline: Chrome, storage, and (optionally) a real render';

    public function handle(): int
    {
        $failures = 0;

        $this->newLine();
        $this->info('=== Environment ===');
        $this->line('APP_ENV: '.config('app.env'));
        $this->line('APP_URL: '.config('app.url'));
        $this->line('PHP: '.PHP_VERSION);

        $renderer = config('receipts.renderer');
        $this->line('Renderer: '.$renderer);

        if ($renderer === 'browserless') {
            $failures += $this->checkBrowserless();
        } else {
            $failures += $this->checkChrome();
            $failures += $this->checkNode();
        }

        $failures += $this->checkStorage();

        if ($invoiceId = $this->argument('invoice')) {
            $failures += $this->checkRender($invoiceId);
        } else {
            $this->newLine();
            $this->comment('Tip: pass an invoice ID to test a real end-to-end render, e.g. `php artisan receipts:diagnose 5`');
        }

        $this->newLine();

        if ($failures > 0) {
            $this->error("{$failures} check(s) FAILED — see above.");

            return self::FAILURE;
        }

        $this->info('All checks passed.');

        return self::SUCCESS;
    }

    private function checkBrowserless(): int
    {
        $this->newLine();
        $this->info('=== Browserless (remote Chrome) ===');

        $url = config('receipts.browserless.url');
        $token = config('receipts.browserless.token');

        $this->line('BROWSERLESS_URL: '.($url ?: '(NOT SET)'));
        $this->line('BROWSERLESS_TOKEN set: '.($token ? 'yes' : 'no'));

        if (! $url) {
            $this->error('  ✗ No endpoint configured — rendering cannot work.');

            return 1;
        }

        // Round-trip a trivial document: proves reachability, auth, and that
        // the service actually returns PDF bytes, all in one shot.
        try {
            $pdf = app(\App\Services\Rendering\BrowserlessRenderer::class)
                ->render('<html><body><h1>ok</h1></body></html>', ['page' => 'A4']);

            $this->line('  ✓ round-trip OK ('.strlen($pdf).' bytes of PDF)');

            return 0;
        } catch (\Throwable $e) {
            $this->error('  ✗ '.get_class($e).': '.$e->getMessage());

            return 1;
        }
    }

    private function checkChrome(): int
    {
        $this->newLine();
        $this->info('=== Headless Chrome ===');

        $configured = config('receipts.chrome_path');
        $this->line('CHROME_PATH config: '.($configured ?: '(not set)'));

        if ($configured && ! str_contains($configured, 'chrome.exe') && ! file_exists($configured)) {
            $this->error("  ✗ CHROME_PATH is set to [{$configured}] but no file exists there.");
        }

        $candidates = [
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/snap/bin/chromium',
        ];

        $found = [];
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $found[] = $path;
                $this->line("  ✓ found: {$path}");
            }
        }

        if (empty($found)) {
            $this->warn('  ! No system Chrome/Chromium binary found.');
            $this->warn('    Not necessarily fatal — puppeteer ships its own (see below).');
        }

        return 0;
    }

    private function checkNode(): int
    {
        $this->newLine();
        $this->info('=== Node / puppeteer ===');

        $nodeModules = config('receipts.node_module_path') ?: base_path('node_modules');
        $this->line("node module path: {$nodeModules}");

        if (! is_dir($nodeModules)) {
            $this->error("  ✗ node_modules directory does not exist.");
            $this->warn('    Hosts that build assets in a separate container (Laravel Cloud');
            $this->warn('    among them) ship only public/build to the runtime container.');

            return 1;
        }
        $this->line('  ✓ node_modules directory exists');

        // This is the exact thing Browsershot's browser.cjs does at runtime:
        // require('puppeteer'). Its absence is a hard failure, regardless of
        // whether a system Chrome binary is installed.
        if (! is_dir($nodeModules.'/puppeteer')) {
            $this->error('  ✗ puppeteer package NOT installed — PDF rendering cannot work.');
            $this->warn('    Install it in the runtime environment, e.g. as a deploy command:');
            $this->warn('      npm install puppeteer --omit=dev');

            return 1;
        }
        $this->line('  ✓ puppeteer package present');

        $cacheDir = config('receipts.puppeteer_cache_dir');
        $this->line('PUPPETEER_CACHE_DIR: '.($cacheDir ?: '(not set — puppeteer default ~/.cache/puppeteer)'));

        if ($cacheDir && ! is_dir($cacheDir)) {
            $this->error("  ✗ cache dir set but does not exist: {$cacheDir}");
            $this->warn('    puppeteer downloaded its browser somewhere else, or not at all.');
            $this->warn('    Set PUPPETEER_CACHE_DIR to the same value at install AND runtime.');

            return 1;
        }

        return 0;
    }

    private function checkStorage(): int
    {
        $this->newLine();
        $this->info('=== Storage ===');

        $failures = 0;

        $this->comment('Note: receipt PDFs are streamed straight to the browser and never');
        $this->comment('stored, so only the uploads disk (business logos) is load-bearing.');
        $this->comment('A failure there degrades gracefully — receipts render without a logo.');
        $this->newLine();

        foreach ([
            'receipts.uploads_disk (logos)' => config('receipts.uploads_disk'),
        ] as $label => $diskName) {
            $this->line("{$label}: {$diskName}");

            if ($diskName === 's3') {
                $this->line('  endpoint: '.(config('filesystems.disks.s3.endpoint') ?: '(not set)'));
                $this->line('  bucket: '.(config('filesystems.disks.s3.bucket') ?: '(NOT SET)'));
                $this->line('  region: '.(config('filesystems.disks.s3.region') ?: '(not set)'));
                $this->line('  path-style: '.var_export(config('filesystems.disks.s3.use_path_style_endpoint'), true));
                $this->line('  key set: '.(config('filesystems.disks.s3.key') ? 'yes' : 'NO'));
                $this->line('  secret set: '.(config('filesystems.disks.s3.secret') ? 'yes' : 'NO'));
            }

            $failures += $this->probeDisk($diskName);
        }

        return $failures;
    }

    /** Round-trips a small file to prove the disk is genuinely writable/readable. */
    private function probeDisk(string $diskName): int
    {
        $key = 'diagnostics/_probe_'.uniqid().'.txt';
        $payload = 'probe';

        try {
            $disk = Storage::disk($diskName);

            // NB: these disks are configured with 'throw' => false, so a
            // failed write returns false rather than raising — hence the
            // explicit check.
            if (! $disk->put($key, $payload)) {
                $this->error("  ✗ write FAILED (put() returned false) on disk [{$diskName}]");
                $this->warn('    Usually bad/missing credentials, wrong endpoint, or (on Cloudflare R2)');
                $this->warn('    AWS_USE_PATH_STYLE_ENDPOINT not set to true.');

                return 1;
            }
            $this->line('  ✓ write ok');

            $read = $disk->get($key);
            if ($read !== $payload) {
                $this->error('  ✗ read back MISMATCH');
                $disk->delete($key);

                return 1;
            }
            $this->line('  ✓ read ok');

            $disk->delete($key);
            $this->line('  ✓ delete ok');

            return 0;
        } catch (\Throwable $e) {
            $this->error('  ✗ '.get_class($e).': '.$e->getMessage());

            return 1;
        }
    }

    private function checkRender(string $invoiceId): int
    {
        $this->newLine();
        $this->info("=== End-to-end render (invoice {$invoiceId}) ===");

        // The global business scope keys off an authenticated user, which
        // there isn't one of in a console context — bypass it.
        $invoice = Invoice::withoutGlobalScopes()->find($invoiceId);

        if (! $invoice) {
            $this->error("  ✗ Invoice {$invoiceId} not found in this environment's database.");

            $available = Invoice::withoutGlobalScopes()
                ->latest('id')
                ->limit(10)
                ->pluck('id');

            if ($available->isEmpty()) {
                $this->warn('    No invoices exist here at all — are you running this against the right environment?');
            } else {
                $this->warn('    Available invoice IDs here: '.$available->implode(', '));
            }

            return 1;
        }

        $layout = $invoice->template ?? 'ledger';
        $this->line("  layout: {$layout}");

        try {
            $document = InvoiceDocumentBuilder::build($invoice, $layout);
            $this->line('  ✓ document built');
        } catch (\Throwable $e) {
            $this->error('  ✗ document build: '.get_class($e).': '.$e->getMessage());

            return 1;
        }

        try {
            $html = Blade::render("pdf.layouts.{$layout}", $document);
            $this->line('  ✓ blade rendered ('.strlen($html).' bytes)');
        } catch (\Throwable $e) {
            $this->error('  ✗ blade render: '.get_class($e).': '.$e->getMessage());

            return 1;
        }

        try {
            $pdf = app(ReceiptRenderer::class)->render($html, $document['meta']);
            $this->line('  ✓ PDF rendered ('.strlen($pdf).' bytes)');
        } catch (\Throwable $e) {
            $this->error('  ✗ PDF render: '.get_class($e).': '.$e->getMessage());
            $this->warn(config('receipts.renderer') === 'browserless'
                ? '    ^ This step needs a reachable, correctly-authenticated Browserless endpoint.'
                : '    ^ This step needs both the puppeteer npm package and a browser binary.');

            return 1;
        }

        return 0;
    }
}
