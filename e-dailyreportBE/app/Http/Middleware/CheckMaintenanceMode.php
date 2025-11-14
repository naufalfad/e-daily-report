<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Cek status maintenance dari DB
        $isMaintenance = SystemSetting::where('setting_key', 'maintenance_mode')
            ->where('setting_value', '1') // Asumsi '1' = Aktif
            ->exists();

        if ($isMaintenance) {
            // 2. Pengecualian: Admin IT boleh lewat
            $user = Auth::guard('sanctum')->user(); // Cek user dari token
            
            if ($user && $user->roles->contains('nama_role', 'Super Admin')) {
                return $next($request); // Silakan lewat, Paduka Admin
            }

            // 3. Blokir User Lain
            return response()->json([
                'message' => 'Sistem sedang dalam perbaikan (Maintenance Mode). Silakan coba beberapa saat lagi.',
                'code' => 'MAINTENANCE_MODE'
            ], 503); // 503 Service Unavailable
        }

        return $next($request);
    }
}