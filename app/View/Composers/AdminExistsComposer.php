<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\View\View;

class AdminExistsComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        // Cache hasil query selama 24 jam untuk menghemat koneksi database
        $adminExists = \Illuminate\Support\Facades\Cache::remember('admin_exists', 86400, function () {
            return User::whereHas('roles', function($query) {
                $query->where('name', 'Admin');
            })->exists();
        });

        $view->with('adminExists', $adminExists);
    }
}
