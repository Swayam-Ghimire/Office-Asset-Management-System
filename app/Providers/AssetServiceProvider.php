<?php
namespace App\Providers;

use App\Models\Asset;
use App\Observers\AssetObserver;
use App\Services\AssetImportService;
use Illuminate\Support\ServiceProvider;

class AssetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // We use singleton so the app only creates one instance of this service
        $this->app->singleton(AssetImportService::class, function ($app) {
            return new AssetImportService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        Asset::observe(AssetObserver::class);
    }
}