<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog; // Pastikan Model ini ada
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * List Log Aktivitas
     * Logic berbeda tiap role (Sesuai UI Document)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ActivityLog::with('user')->latest();

        // 1. Admin/Kadis: Bisa lihat SEMUA log (UI-20, UI-22)
        if ($user->roles->contains('nama_role', 'Super Admin') || $user->roles->contains('nama_role', 'Kadis')) {
            // No filter (Show All)
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }
        // 2. Penilai: Bisa lihat log BAWAHAN + Diri Sendiri (UI-13)
        elseif ($user->roles->contains('nama_role', 'Penilai')) {
            $bawahanIds = User::where('atasan_id', $user->id)->pluck('id');
            $bawahanIds[] = $user->id; // Include diri sendiri
            $query->whereIn('user_id', $bawahanIds);
        }
        // 3. Pegawai: Hanya lihat log DIRI SENDIRI (UI-07)
        else {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->paginate(15));
    }
}