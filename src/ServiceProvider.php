<?php

declare(strict_types = 1);

namespace McMatters\SingleRole;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class SingleRoleServiceProvider
 *
 * @package McMatters\SingleRole
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/single-role.php' => $this->app->configPath('single-role.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../migrations' => $this->app->databasePath('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->resourcePath('lang'),
        ], 'translations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'single-role');

        $this->registerBladeDirectives();
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/single-role.php', 'single-role');
    }

    /**
     * @return void
     */
    protected function registerBladeDirectives(): void
    {
        Blade::if('role', function (string $role) {
            return Auth::check() && Auth::user()->hasRole($role);
        });

        Blade::if('permission', function (string $permission) {
            return Auth::check() && Auth::user()->hasPermissions($permission);
        });
    }
}
