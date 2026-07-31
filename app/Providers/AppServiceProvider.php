<?php

namespace App\Providers;

use App\Services\Rendering\BrowserlessRenderer;
use App\Services\Rendering\BrowsershotRenderer;
use App\Services\Rendering\ReceiptRenderer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ReceiptRenderer::class, fn () => match (config('receipts.renderer')) {
            'browserless' => new BrowserlessRenderer(),
            'browsershot' => new BrowsershotRenderer(),
            default => throw new \InvalidArgumentException(
                'Unknown receipts.renderer ['.config('receipts.renderer').']. '
                .'Expected "browsershot" or "browserless".'
            ),
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
