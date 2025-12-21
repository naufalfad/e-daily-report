<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan daftar aktivitas log dengan filter yang robust.
     * Menggunakan pendekatan Eloquent 'when' untuk query dinamis.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 1. BASE QUERY & EAGER LOADING
        // Kita load relasi user, roles, dan jabatan untuk meminimalisir N+1 Query
        $query = ActivityLog::with([
            'user:id,name,nip,jabatan_id',
            'user.roles:id,nama_role',
            'user.jabatan:id,nama_jabatan' 
        ]);

        // 2. AUTHORIZATION SCOPE (Business Logic)
        // Cek apakah user punya role Super Admin / Admin
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Admin');

        if (!$isSuperAdmin) {
            // User Biasa: Hanya melihat log miliknya sendiri DAN log sistem (null)
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id'); 
            });
        }

        // 3. STANDARDIZED FILTERS (Implementation Phase 1)
        
        // A. Filter Search (Keyword)
        // Mencakup: Deskripsi Log, Nama User, dan NIP
        $query->when($request->search, function ($q, $search) {
            // Deteksi database driver untuk Case Insensitive Search (PostgreSQL friendly)
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $q->where(function ($sub) use ($search, $like) {
                $sub->where('deskripsi_aktivitas', $like, "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search, $like) {
                        $u->where('name', $like, "%{$search}%")
                          ->orWhere('nip', $like, "%{$search}%");
                    });
            });
        });

        // B. Filter Bulan (Month) - Input: 1-12
        $query->when($request->month, function ($q, $month) {
            $q->whereMonth('timestamp', $month);
        });

        // C. Filter Tahun (Year) - Input: 2024, 2025, dst
        $query->when($request->year, function ($q, $year) {
            $q->whereYear('timestamp', $year);
        });

        // D. Filter Role (Opsional: Admin ingin filter log berdasarkan role user)
        // Input: 'Staf', 'Penilai', 'Kadis'
        $query->when($request->role, function ($q, $roleName) {
            $q->whereHas('user.roles', function ($r) use ($roleName) {
                $r->where('nama_role', $roleName);
            });
        });

        // E. Filter Tanggal Spesifik (Opsional, support legacy)
        $query->when($request->date, fn($q, $date) => $q->whereDate('timestamp', $date));

        // 4. SORTING & PAGINATION
        // Menggunakan latest() agar log terbaru selalu di atas
        // Pagination default 15, tapi bisa di-override via request
        $perPage = $request->input('per_page', 15);
        $logs = $query->latest('timestamp')->paginate($perPage);

        // 5. DATA TRANSFORMATION
        // Mapping data agar mudah dibaca oleh Frontend (DataTable/Card)
        $logs->getCollection()->transform(function ($log) {
            $user = $log->user;
            
            // Ambil role pertama sebagai label utama
            $roleName = '-';
            if ($user && $user->roles->isNotEmpty()) {
                $roleName = $user->roles->first()->nama_role;
            }

            // Timestamp formatting
            $ts = $log->timestamp ? Carbon::parse($log->timestamp) : null;

            return [
                'id' => $log->id,
                'user_name' => $user->name ?? 'SYSTEM',
                'user_nip'  => $user->nip ?? '-',
                'user_role' => $roleName,
                'jabatan'   => $user->jabatan->nama_jabatan ?? '-',
                'deskripsi' => $log->deskripsi_aktivitas,
                'ip_address'=> $log->ip_address ?? '-', // Jika kolom ada
                'user_agent'=> $log->user_agent ?? '-', // Jika kolom ada
                'date_formatted' => $ts ? $ts->format('d M Y') : '-',
                'time_formatted' => $ts ? $ts->format('H:i') : '-',
                'time_ago'  => $ts ? $ts->diffForHumans() : '-',
                'full_timestamp' => $ts ? $ts->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json($logs);
    }
}