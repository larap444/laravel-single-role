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
     * Boot provider.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/single-role.php' => config_path('single-role.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang'),
        ], 'translations');

        $this->registerBladeDirectives();
    }

    /**
     * Register application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/single-role.php', 'single-role');
    }

    /**
     * @return void
     */
    protected function registerBladeDirectives()
    {
        $auth = Auth::class;

        Blade::directive('role', function ($expression) use ($auth) {
            return "<?php if ({$auth}::check() && {$auth}::user()->hasRole({$expression})): ?>";
        });

        Blade::directive('endrole', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('permission', function ($expression) use ($auth) {
            return "<?php if ({$auth}::check() && {$auth}::user()->hasPermissions({$expression})): ?>";
        });

        Blade::directive('endpermission', function () {
            return '<?php endif; ?>';
        });
    }
}
