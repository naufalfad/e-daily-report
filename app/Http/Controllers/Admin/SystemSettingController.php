<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting; // Pastikan Model ini ada
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /**
     * Get All Settings
     */
    public function index()
    {
        // Return key-value pair agar mudah dipakai Frontend
        $settings = SystemSetting::pluck('setting_value', 'setting_key');
        return response()->json($settings);
    }

    /**
     * Update Settings (Bulk Update)
     */
    public function update(Request $request)
    {
        // Contoh input: { "maintenance_mode": "1", "app_name": "E-Daily Mimika" }
        $data = $request->all();

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        return response()->json(['message' => 'Pengaturan sistem diperbarui']);
    }
}