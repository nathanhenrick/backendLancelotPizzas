<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->isProduction() && config('app.debug')) {
            throw new RuntimeException('APP_DEBUG precisa ser false em produção.');
        }

        $proxies = config('app.trusted_proxies');
        if ($proxies) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        RateLimiter::for('login', fn(Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perMinute(5)->by('login-email|' . (Str::lower((string) $request->input('email')) ?: $request->ip())),
        ]);

        RateLimiter::for('pedidos', fn(Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perMinute(120)->by('pedidos'),
        ]);
    }
}
