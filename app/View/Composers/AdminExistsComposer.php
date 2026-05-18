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
        $adminExists = User::whereHas('roles', function($query) {
            $query->where('name', 'Admin');
        })->exists();

        $view->with('adminExists', $adminExists);
    }
}
