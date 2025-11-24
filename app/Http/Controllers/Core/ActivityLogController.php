<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Load log + user + role
        $query = ActivityLog::with([
            'user:id,name,nip,jabatan_id',
            'user.roles:id,nama_role'
        ]);

        // Check if Super Admin
        $isSuperAdmin = $user->roles()->where('nama_role', 'Super Admin')->exists();

        if (!$isSuperAdmin) {
            // User biasa → lihat log sendiri + log system
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id'); // log system
            });
        }

        // ==================================================
        // PENCARIAN
        // ==================================================
        if ($request->filled('search')) {
            $search = $request->search;
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($search, $like) {
                $q->where('deskripsi_aktivitas', $like, "%{$search}%")
                  ->orWhereHas('user', function ($subQ) use ($search, $like) {
                      $subQ->where('name', $like, "%{$search}%");
                  });
            });
        }

        // ==================================================
        // FILTER TANGGAL
        // ==================================================
        if ($request->filled('date')) {
            $query->whereDate('timestamp', $request->date);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('timestamp', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('timestamp', '<=', $request->date_to);
        }

        // ==================================================
        // SORTING & PAGINATION
        // ==================================================
        $logs = $query->orderBy('timestamp', 'desc')->paginate(15);

        // ==================================================
        // TRANSFORM OUTPUT (AMAN DARI NULL)
        // ==================================================
        $logs->getCollection()->transform(function ($log) {
            $user = $log->user;
            $role = $user ? optional($user->roles->first())->nama_role ?? '-' : '-';
            $timestamp = $log->timestamp ? Carbon::parse($log->timestamp) : null;

            // Tentukan tipe log: create / update / system
            $tipe =
                $log->action === 'create' ? 'create' :
                ($log->action === 'update' ? 'update' : 'system');

            return [
                'id' => $log->id,
                'user_name' => $user->name ?? 'SYSTEM',
                'user_role' => $role,
                'deskripsi_aktivitas' => $log->deskripsi_aktivitas ?? $log->description ?? '',
                'action' => $log->action,
                'tipe' => $tipe,
                'timestamp' => $timestamp ? $timestamp->format('Y-m-d H:i:s') : null,
                'time_ago' => $timestamp ? $timestamp->diffForHumans() : '-',
            ];
        });

        return response()->json($logs);
    }
}
