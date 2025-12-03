<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache; 
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. --- FORCE LOAD USER FOR API REQUESTS (Perbaikan Admin Lock-Out) ---
        // Jika request API dan user belum dimuat, coba paksa load user via Sanctum guard
        $user = Auth::user();
        if (!$user && $request->is('api/*')) {
             $user = Auth::guard('sanctum')->user();
        }

        // 2. --- BYPASS EKSPILISIT UNTUK ADMIN KE HALAMAN SETTING ---
        // Jika Super Admin mencoba mengakses endpoint Pengaturan Sistem (GET atau POST), biarkan lewat.
        if ($user && $user->hasRole('Super Admin') && $request->is('api/admin/settings')) {
            return $next($request); 
        }

        // 3. --- LOAD MAINTENANCE STATUS (Dari Cache/DB) ---
        // Cache tetap digunakan untuk performa, kecuali jika Admin mem-bypass.
        $maintenanceStatus = Cache::remember('maintenance_mode_status', 60, function () {
            return SystemSetting::where('setting_key', 'maintenance_mode')
                                ->value('setting_value');
        });

        // 4. --- CHECK STATUS DAN BLOKIR NON-ADMIN ---
        if ($maintenanceStatus === '1') {
            
            // Pengecualian 1: Login, Maintenance Page
            if ($request->routeIs('login') || $request->routeIs('maintenance') || $request->is('maintenance')) {
                return $next($request);
            }

            // Pengecualian 2: Super Admin (yang sudah terotentikasi)
            if ($user && $user->hasRole('Super Admin')) {
                return $next($request);
            }

            // BLOKIR: Non-Admin/Non-Bypass requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Sistem sedang dalam mode pemeliharaan. Hanya Administrator yang dapat mengakses.',
                    'code' => 'MAINTENANCE_MODE'
                ], 503);
            }
            
            // Redirect Web
            return redirect()->route('maintenance');
        }

        // Lolos Maintenance Check
        return $next($request);
    }
}