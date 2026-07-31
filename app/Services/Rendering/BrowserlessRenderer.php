<?php

namespace App\Services\Rendering;

use Illuminate\Support\Facades\Http;

/**
 * Renders receipt PDFs by POSTing HTML to a remote headless-Chrome service
 * that speaks the Browserless `/pdf` API (browserless.io, or a self-hosted
 * `ghcr.io/browserless/chromium` container).
 *
 * Exists because {@see BrowsershotRenderer} shells out to Node and requires
 * the `puppeteer` npm package plus a Chromium binary in the runtime
 * environment. Hosts that build front-end assets in a separate container
 * (Laravel Cloud among them) don't ship node_modules to the runtime
 * container, and installing it at deploy time doesn't persist there — so
 * Browsershot cannot work on those platforms at all. This renderer needs
 * nothing but outbound HTTP, so it works anywhere.
 *
 * Output is still real Chrome, so the layouts render identically to local
 * Browsershot output — flexbox, color-mix(), clip-path and all.
 */
class BrowserlessRenderer implements ReceiptRenderer
{
    public function render(string $html, array $meta = []): string
    {
        $endpoint = config('receipts.browserless.url');
        $token = config('receipts.browserless.token');

        if (! $endpoint) {
            throw new \RuntimeException(
                'No Browserless endpoint configured. Set BROWSERLESS_URL (and usually '
                .'BROWSERLESS_TOKEN), or switch RECEIPT_RENDERER back to "browsershot".'
            );
        }

        $response = Http::timeout((int) config('receipts.browserless.timeout'))
            ->withOptions(['stream' => false])
            ->when($token, fn ($request) => $request->withQueryParameters(['token' => $token]))
            // Browserless routes on the Accept header, and this endpoint
            // returns PDF bytes — asking for JSON matches no route and 404s
            // with a message about the *endpoint* being wrong, which sends
            // you looking in entirely the wrong place.
            ->accept('application/pdf')
            ->post(rtrim($endpoint, '/').'/pdf', [
                'html' => $html,
                'options' => $this->pdfOptions($meta),
            ]);

        if (! $response->successful()) {
            // The body carries the actual reason (bad token, quota exceeded,
            // malformed options); without it the failure is indistinguishable
            // from a network problem.
            throw new \RuntimeException(
                "Browserless PDF render failed with HTTP {$response->status()}: "
                .mb_strimwidth($response->body(), 0, 500, '…')
            );
        }

        $pdf = $response->body();

        // A 200 carrying HTML/JSON rather than PDF bytes means the service
        // reported a problem in-band; treating it as a PDF would persist a
        // corrupt file that only fails later at download time.
        if (! str_starts_with($pdf, '%PDF')) {
            throw new \RuntimeException(
                'Browserless returned a non-PDF response: '.mb_strimwidth($pdf, 0, 500, '…')
            );
        }

        return $pdf;
    }

    /** @return array<string, mixed> */
    private function pdfOptions(array $meta): array
    {
        $options = [
            'printBackground' => true,
            'margin' => ['top' => '0mm', 'right' => '0mm', 'bottom' => '0mm', 'left' => '0mm'],
        ];

        // Mirrors BrowsershotRenderer: the thermal layout is a continuous
        // 80mm roll, which needs explicit dimensions because Chrome's PDF
        // export requires a concrete page size.
        if (($meta['page'] ?? 'A4') === '80mm') {
            return $options + ['width' => '80mm', 'height' => '250mm'];
        }

        return $options + ['format' => $meta['page'] ?? 'A4'];
    }
}
