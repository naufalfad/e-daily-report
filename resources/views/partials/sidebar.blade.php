@php
// Definisi menu per role (global)
$menusByRole = [

    // ==================== ROLE STAF ====================
    'staf' => [
        ['key' => 'dashboard', 'label' => 'Dashboard',       'icon' => 'home',      'route' => route('staf.dashboard')],
        ['key' => 'lkh',       'label' => 'Input LKH',       'icon' => 'file-edit', 'route' => route('staf.input-lkh')],
        ['key' => 'skp',       'label' => 'Input SKP',       'icon' => 'doc-skp',   'route' => route('staf.input-skp')],
        ['key' => 'map',       'label' => 'Peta Aktivitas',  'icon' => 'map-pin',   'route' => route('staf.peta-aktivitas')],
        ['key' => 'riwayat',   'label' => 'Riwayat',         'icon' => 'history',   'route' => route('staf.riwayat-lkh')],
        ['key' => 'log',       'label' => 'Log Aktivitas',   'icon' => 'clock',     'route' => route('staf.log-aktivitas')],
    ],

    // ==================== ROLE PENILAI ====================
    'penilai' => [
        ['key' => 'dashboard',     'label' => 'Dashboard',        'icon' => 'home',           'route' => route('penilai.dashboard')],
        ['key' => 'input-laporan', 'label' => 'Input Laporan',    'icon' => 'file-edit',      'route' => route('penilai.input-laporan')],
        ['key' => 'input-skp',     'label' => 'Input SKP',        'icon' => 'doc-skp',        'route' => route('penilai.input-skp')],
        ['key' => 'validasi',      'label' => 'Validasi Laporan', 'icon' => 'validation',     'route' => route('penilai.validasi-laporan')],
        ['key' => 'skoring',       'label' => 'Skoring Kinerja',  'icon' => 'skoring',        'route' => route('penilai.skoring-kinerja')],
        ['key' => 'map',           'label' => 'Peta Aktivitas',   'icon' => 'map-pin',        'route' => route('penilai.peta-aktivitas')],
        ['key' => 'riwayat',       'label' => 'Riwayat',          'icon' => 'history',        'route' => route('penilai.riwayat')],
        ['key' => 'log',           'label' => 'Log Aktivitas',    'icon' => 'clock',          'route' => route('penilai.log-aktivitas')],
        ['key' => 'pengumuman',    'label' => 'Pengumuman',       'icon' => 'announcement',   'route' => route('penilai.pengumuman.index')],
    ],

    // ==================== ROLE KEPALA DINAS ====================
    'kadis' => [
        ['key' => 'dashboard', 'label' => 'Dashboard',        'icon' => 'home',       'route' => route('kadis.dashboard')],
        ['key' => 'validasi',  'label' => 'Validasi Laporan', 'icon' => 'validation', 'route' => route('kadis.validasi-laporan')],
        ['key' => 'log',       'label' => 'Log Aktivitas',    'icon' => 'clock',      'route' => route('kadis.log-aktivitas')],
        ['key' => 'pengumuman','label' => 'Pengumuman',       'icon' => 'announcement', 'route' => route('kadis.pengumuman.index')],
        ['key' => 'skoring-bidang', 'label' => 'Skoring Kinerja Per Bidang', 'icon' => 'skoring', 'route' => route('kadis.skoring-bidang')],
        ['key' => 'map',           'label' => 'Peta Aktivitas',   'icon' => 'map-pin',        'route' => route('kadis.peta-aktivitas')],
    ],

    // ==================== ROLE ADMIN ====================
    'admin' => [
        ['key' => 'dashboard',         'label' => 'Dashboard',         'icon' => 'home',              'route' => route('admin.dashboard')],
        ['key' => 'manajemen-pegawai', 'label' => 'Manajemen Pegawai', 'icon' => 'manajemen-pegawai', 'route' => route('admin.manajemen-pegawai')],
        ['key' => 'akun-pengguna',     'label' => 'Akun Pengguna',     'icon' => 'akun',              'route' => route('admin.akun-pengguna')],
        ['key' => 'pengaturan',        'label' => 'Pengaturan Sistem', 'icon' => 'setting',           'route' => route('admin.pengaturan-sistem')],
        ['key' => 'log',               'label' => 'Log Aktivitas',     'icon' => 'clock',             'route' => route('admin.log-aktivitas')],
    ],
];

// Mapping alias role kalau dari Auth beda
$roleKey = $role ?? 'staf';
if ($roleKey === 'kepala-dinas') {
    $roleKey = 'kadis';
}

$activeMenu = $active ?? 'dashboard';
$menus = $menusByRole[$roleKey] ?? $menusByRole['staf'];

// [FASE 4] INJEKSI MENU PROFIL UNTUK SEMUA ROLE
// Kita tambahkan di akhir array $menus agar muncul paling bawah sebelum logout
$menus[] = [
    'key'   => 'profil',
    'label' => 'Profil Saya',
    'icon'  => 'profil',       // Key icon baru
    'route' => route('profil.edit')
];

// List icon yang TIDAK BOLEH pakai invert filter (karena sudah putih/warna khusus)
$iconsWithoutInvert = [
    'home.svg',
    'maps.svg',
    'skoring.svg',
    'map-pin.svg',
];

// Icon file map
$iconMap = [
    'home'              => 'home.svg',
    'file-edit'         => 'doc-laporan.svg',
    'doc-skp'           => 'doc-skp.svg',
    'map-pin'           => 'maps.svg',
    'history'           => 'history.svg',
    'clock'             => 'log.svg',
    'settings'          => 'settings.svg',
    'announcement'      => 'pengumuman.svg',
    'validation'        => 'validation.svg',
    'skoring'           => 'skoring.svg',
    'manajemen-pegawai' => 'manajemen-pegawai.svg',
    'akun'              => 'akun.svg',
    'setting'           => 'setting.svg',
    'profil'            => 'profile.svg', // [FASE 4] Mapping icon profil baru
];
@endphp

<aside id="sidebar" class="fixed lg:sticky inset-y-0 left-0 z-40 -translate-x-full lg:translate-x-0 transition-transform duration-200
           lg:top-5 w-[340px] text-white
           lg:h-[calc(100dvh-40px)]
           bg-[#1C7C54] rounded-[20px] overflow-hidden
           shadow-[0_12px_30px_rgba(0,0,0,0.18)] ring-1 ring-black/5
           px-4 pt-6 pb-5
           flex flex-col">

    {{-- Header Sidebar --}}
    <div class="flex flex-col items-center text-center mb-8 shrink-0">
        <img src="{{ asset('img/logo-kab-mimika.png') }}" class="h-[114px] w-[152px] object-contain mb-3" />
        <div class="leading-tight flex flex-col items-center gap-[6px]">
            <div class="font-semibold text-[20px]">Badan Pendapatan Daerah</div>
            <div class="text-[17px] font-normal">Kabupaten Mimika</div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto pr-1 sidebar-scroll">
        @foreach ($menus as $menu)
            @php
                $iconFile = $iconMap[$menu['icon']] ?? 'home.svg';
                // Deteksi active menu berdasarkan key ATAU URL route-nya
                $isActive = ($activeMenu === $menu['key'] || request()->url() === $menu['route']);
                $shouldInvert = $isActive && !in_array($iconFile, $iconsWithoutInvert);
            @endphp

            <a href="{{ $menu['route'] }}" class="flex text-[17px] items-center gap-3 px-4 py-3 rounded-xl transition
                {{ $isActive ? 'bg-[#36B37E] text-white' : 'text-white/90 hover:bg-[#36B37E]/70' }}">

                <img src="{{ asset('assets/icon/' . $iconFile) }}"
                    class="h-5 w-5 {{ $shouldInvert ? 'filter invert brightness-0' : '' }}" />

                <span>{{ $menu['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Footer Logout --}}
    <div class="mt-6 pt-4 shrink-0">
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="button" id="btn-logout"
                class="w-full flex text-[17px] items-center gap-3 px-4 py-3 hover:bg-[#36B37E]/70 rounded-xl transition">
                <img src="{{ asset('assets/icon/logout.svg') }}" class="h-5 w-5" />
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>