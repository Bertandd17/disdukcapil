<?php

namespace App\Providers;

use App\Models\Berita_Model;
use App\View\Composers\AdminExistsComposer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        Route::model('berita', Berita_Model::class);

        // Register AdminExistsComposer untuk semua views
        View::composer('*', AdminExistsComposer::class);
    }
}
