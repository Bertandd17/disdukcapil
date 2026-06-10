<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider untuk custom Blade directives
 *
 * Menambahkan directive untuk security:
 * - @maskNik($nik) - Mask NIK untuk display
 * - @formatNik($nik) - Format NIK dengan spasi
 */
class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share isAdmin variable ke semua views (cached)
        view()->composer('*', function ($view) {
            $isAdmin = false;
            if (auth()->check() && auth()->user()) {
                $isAdmin = auth()->user()->hasRole('Admin');
            }
            $view->with('isAdmin', $isAdmin);
        });

        Blade::directive('assetV', function (string $expression): string {
            $expression = trim($expression);

            return "<?php echo e(app(\\App\\Support\\AssetVersion::class)->url({$expression})); ?>";
        });
    }
}
