<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminExistsComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        $viewName = $view->name();

        if (str_starts_with($viewName, 'errors::') || str_starts_with($viewName, 'errors.')) {
            $view->with('adminExists', false);
            return;
        }

        try {
            $adminExists = User::whereHas('roles', function($query) {
                $query->where('name', 'Admin');
            })->exists();
        } catch (\Throwable $e) {
            Log::warning('AdminExistsComposer: query gagal, default adminExists=false', [
                'view' => $viewName,
                'error' => $e->getMessage(),
            ]);
            $adminExists = false;
        }

        $view->with('adminExists', $adminExists);
    }
}
