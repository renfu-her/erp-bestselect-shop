<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        if (env('REDIRECT_HTTPS')) {
            Url::forceScheme('https');
            $this->app['request']->server->set('HTTPS','on');
        }

        Sanctum::authenticateAccessTokensUsing(
            static function (PersonalAccessToken $accessToken, bool $is_valid) {
                // 令牌能力
                // 該令牌只可打一次
                if ($accessToken->can('read:once')) {
                    return $is_valid && $accessToken->last_used_at === null;
                }
                // 該令牌一分鐘內有效
                else if ($accessToken->can('read:limited')) {
                    return $is_valid && $accessToken->created_at->gt(now()->subMinutes(1));
                }
                return $is_valid;
            }
        );
    }
}
