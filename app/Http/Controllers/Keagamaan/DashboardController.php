<?php

namespace App\Http\Controllers\Keagamaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard keagamaan
     */
    public function index(Request $request)
    {
        Log::info('=== Keagamaan Dashboard Accessed ===');
        Log::info('Is Authenticated: ' . (Auth::check() ? 'YES' : 'NO'));
        Log::info('Auth ID: ' . (Auth::id() ?: 'NULL'));

        if (Auth::check()) {
            $user = Auth::user();
            Log::info('User: ' . $user->username);
            Log::info('User Name: ' . $user->name);
            Log::info('Roles: ' . json_encode($user->roles->pluck('name')->toArray()));
        }

        $user = Auth::user();
        $page_title = 'Dashboard Keagamaan';

        return view('keagamaan.dashboard', compact('user', 'page_title'));
    }
}
