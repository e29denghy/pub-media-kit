<?php

namespace App\Providers;

use App\Publishing\Adapters\WechatOfficialAccountAdapter;
use App\Publishing\Adapters\XApiAdapter;
use App\Publishing\Adapters\ZhihuAdapter;
use App\Publishing\PlatformAdapterRegistry;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlatformAdapterRegistry::class, function ($app) {
            return new PlatformAdapterRegistry([
                $app->make(WechatOfficialAccountAdapter::class),
                $app->make(ZhihuAdapter::class),
                $app->make(XApiAdapter::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
