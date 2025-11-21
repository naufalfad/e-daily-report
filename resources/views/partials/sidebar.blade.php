@php
    // Semua menu berdasarkan role
    $menusByRole = [
        'staf' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => route('staf.dashboard')],
            ['key' => 'lkh', 'label' => 'Input LKH', 'icon' => 'file-edit', 'route' => route('staf.input-lkh')],
            ['key' => 'skp', 'label' => 'Input SKP', 'icon' => 'doc-skp', 'route' => route('staf.input-skp')],
            ['key' => 'map', 'label' => 'Peta Aktivitas', 'icon' => 'map-pin', 'route' => route('staf.peta-aktivitas')],
            ['key' => 'riwayat', 'label' => 'Riwayat', 'icon' => 'history', 'route' => route('staf.riwayat-lkh')],
            ['key' => 'log', 'label' => 'Log Aktivitas', 'icon' => 'clock', 'route' => route('staf.log-aktivitas')],
        ],

        'penilai' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => route('penilai.dashboard')],
            ['key' => 'input-laporan', 'label' => 'Input Laporan', 'icon' => 'file-edit', 'route' => route('penilai.input-laporan')],
            ['key' => 'skp', 'label' => 'Input SKP', 'icon' => 'doc-skp', 'route' => route('penilai.input-skp')],
            ['key' => 'validasi', 'label' => 'Validasi Laporan', 'icon' => 'validation', 'route' => route('penilai.validasi-laporan')],
            ['key' => 'skoring', 'label' => 'Skoring Kinerja', 'icon' => 'skoring', 'route' => route('penilai.skoring-kinerja')],
            ['key' => 'map', 'label' => 'Peta Aktivitas', 'icon' => 'map-pin', 'route' => route('penilai.peta-aktivitas')],
            ['key' => 'riwayat', 'label' => 'Riwayat', 'icon' => 'history', 'route' => route('penilai.riwayat')],
            ['key' => 'log', 'label' => 'Log Aktivitas', 'icon' => 'clock', 'route' => route('penilai.log-aktivitas')],
            ['key' => 'pengumuman', 'label' => 'Pengumuman', 'icon' => 'announcement', 'route' => route('penilai.pengumuman')],
        ],

        'admin' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => route('admin.dashboard')],
            ['key' => 'pengaturan', 'label' => 'Pengaturan Sistem', 'icon' => 'settings', 'route' => route('admin.pengaturan-sistem')],
        ],
    ];

    // role dari layout
    $roleKey = $role ?? 'staf';

    // buat safety kalau role tidak ada
    $menus = $menusByRole[$roleKey] ?? $menusByRole['staf'];

    $activeMenu = $active ?? '';
@endphp


<aside id="sidebar"
    class="fixed lg:sticky inset-y-0 left-0 z-40 -translate-x-full lg:translate-x-0
           w-[340px] bg-[#1C7C54] text-white rounded-[20px] px-4 pt-6 pb-5">

    {{-- Header --}}
    <div class="flex flex-col items-center mb-8">
        <img src="{{ asset('img/logo-kab-mimika.png') }}" class="h-[114px] w-[152px] mb-3" />
        <div class="text-center leading-tight">
            <div class="font-semibold text-[20px]">Badan Pendapatan Daerah</div>
            <div class="text-[17px] font-normal">Kabupaten Mimika</div>
        </div>
    </div>

    {{-- Navigasi --}}
    <nav class="flex-1 flex flex-col gap-1 overflow-y-auto no-scrollbar">
        @foreach ($menus as $menu)
            <a href="{{ $menu['route'] }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-[17px] transition
                      {{ $activeMenu === $menu['key'] ? 'bg-[#36B37E] text-white' : 'text-white/90 hover:bg-[#36B37E]/70' }}">

                <img src="{{ asset('/assets/icon/' . $menu['icon'] . '.svg') }}"
                     class="h-5 w-5 {{ $activeMenu === $menu['key'] ? 'invert brightness-0' : '' }}" />

                <span>{{ $menu['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Logout --}}
    <div class="mt-6">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="flex w-full items-center gap-3 px-4 py-3 rounded-xl hover:bg-[#36B37E]/70">
                <img src="{{ asset('/assets/icon/logout.svg') }}" class="h-5 w-5"/>
                <span>Logout</span>
            </button>
        </form>
    </div>

</aside>
