<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    /**
     * Menampilkan halaman Landing Page.
     * Mengatur dynamic routing berdasarkan status autentikasi dan role.
     */
    public function index()
    {
        // Default state: User belum login
        $targetUrl = route('login');
        $buttonText = 'Login';

        // Jika user sudah memiliki session aktif
        if (Auth::check()) {
            $user = Auth::user();

            // Ambil nama role, fallback ke 'Staf' jika terjadi anomali data
            $userRole = $user->roles->first()->nama_role ?? 'Staf';

            // Mapping nama_role dari DB ke Route Name dashboard masing-masing
            $roleMap = [
                'Super Admin'   => 'admin.dashboard',
                'Administrator' => 'admin.dashboard',
                'Kadis'         => 'kadis.dashboard',
                'Penilai'       => 'penilai.dashboard',
                'Staf'          => 'staf.dashboard'
            ];

            // Cari route name berdasarkan role, fallback ke dashboard staf
            $routeName = $roleMap[$userRole] ?? 'staf.dashboard';

            $targetUrl = route($routeName);
            $buttonText = 'Buka Dashboard';
        }

        // Lempar variabel ke view landing page
        return view('landing', compact('targetUrl', 'buttonText'));
    }
}
