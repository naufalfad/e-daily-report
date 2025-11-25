@php
// Definisi menu per role (global)
$menusByRole = [
// ==================== ROLE STAF ====================
'staf' => [
['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => route('staf.dashboard')],
['key' => 'lkh', 'label' => 'Input LKH', 'icon' => 'file-edit', 'route' => route('staf.input-lkh')],
['key' => 'skp', 'label' => 'Input SKP', 'icon' => 'doc-skp', 'route' => route('staf.input-skp')],
['key' => 'map', 'label' => 'Peta Aktivitas', 'icon' => 'map-pin', 'route' => route('staf.peta-aktivitas')],
['key' => 'riwayat', 'label' => 'Riwayat', 'icon' => 'history', 'route' => route('staf.riwayat-lkh')],
['key' => 'log', 'label' => 'Log Aktivitas', 'icon' => 'clock', 'route' => route('staf.log-aktivitas')],
],

// ==================== ROLE PENILAI ====================
'penilai' => [
['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => route('penilai.dashboard')],
[
'key' => 'input-laporan',
'label' => 'Input Laporan',
'icon' => 'file-edit',
'route' => route('penilai.input-laporan')
],
['key' => 'input-skp', 'label' => 'Input SKP', 'icon' => 'doc-skp', 'route' => route('penilai.input-skp')],
[
'key' => 'validasi',
'label' => 'Validasi Laporan',
'icon' => 'validation',
'route' => route('penilai.validasi-laporan')
],
['key' => 'skoring', 'label' => 'Skoring Kinerja', 'icon' => 'skoring', 'route' => route('penilai.skoring-kinerja')],
['key' => 'map', 'label' => 'Peta Aktivitas', 'icon' => 'map-pin', 'route' => route('penilai.peta-aktivitas')],
['key' => 'riwayat', 'label' => 'Riwayat', 'icon' => 'history', 'route' => route('penilai.riwayat')],
['key' => 'log', 'label' => 'Log Aktivitas', 'icon' => 'clock', 'route' => route('penilai.log-aktivitas')],
// [PERBAIKAN] Route diarahkan ke index group
['key' => 'pengumuman', 'label' => 'Pengumuman', 'icon' => 'announcement', 'route' =>
route('penilai.pengumuman.index')],
],

// ==================== ROLE KEPALA DINAS ====================
// pakai key 'kadis' sebagai utama
'kadis' => [
['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => route('kadis.dashboard')],
[
'key' => 'validasi',
'label' => 'Validasi Laporan',
'icon' => 'validation',
'route' => route('kadis.validasi-laporan')
],
['key' => 'log', 'label' => 'Log Aktivitas', 'icon' => 'clock', 'route' => route('kadis.log-aktivitas')],
],

// ==================== ROLE ADMIN ====================
'admin' => [
['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => route('admin.dashboard')],
[
'key' => 'manajemen-pegawai',
'label' => 'Manajemen Pegawai',
'icon' => 'manajemen-pegawai',
'route' => route('admin.manajemen-pegawai')
],
['key' => 'akun-pengguna', 'label' => 'Akun Pengguna', 'icon' => 'akun', 'route' => route('admin.akun-pengguna')],
[
'key' => 'pengaturan',
'label' => 'Pengaturan Sistem',
'icon' => 'setting',
'route' => route('admin.pengaturan-sistem')
],
['key' => 'log', 'label' => 'Log Aktivitas', 'icon' => 'clock', 'route' => route('admin.log-aktivitas')],
],
];

// Mapping alias role kalau dari DB/Auth lu namanya beda
$roleKey = $role ?? 'staf';
if ($roleKey === 'kepala-dinas') {
$roleKey = 'kadis';
}

$activeMenu = $active ?? 'dashboard';
$menus = $menusByRole[$roleKey] ?? $menusByRole['staf'];

// Icon map cukup didefinisikan sekali
$iconMap = [
'home' => 'home.svg',
'file-edit' => 'doc-laporan.svg',
'doc-skp' => 'doc-skp.svg',
'map-pin' => 'maps.svg',
'history' => 'history.svg',
'clock' => 'log.svg',
'settings' => 'settings.svg',
'announcement' => 'pengumuman.svg',
'validation' => 'validation.svg',
'skoring' => 'skoring.svg',
'manajemen-pegawai' => 'manajemen-pegawai.svg',
'akun' => 'akun.svg',
'setting' => 'setting.svg',
];
@endphp


<aside id="sidebar" class="fixed lg:sticky inset-y-0 left-0 z-40 -translate-x-full lg:translate-x-0
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

            {{-- Pastikan file icon benar ada di public/assets/icon --}}
            <img src="{{ asset('/assets/icon/' . ($iconMap[$menu['icon']] ?? $menu['icon'] . '.svg')) }}"
                class="h-5 w-5 {{ $activeMenu === $menu['key'] ? 'invert brightness-0' : '' }}" />

            <span>{{ $menu['label'] }}</span>
        </a>
        @endforeach
    </nav>

    {{-- Logout --}}
    <div class="mt-6">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button id="btn-logout" class="flex w-full items-center gap-3 px-4 py-3 rounded-xl hover:bg-[#36B37E]/70">
                <img src="{{ asset('/assets/icon/logout.svg') }}" class="h-5 w-5" />
                <span>Logout</span>
            </button>
        </form>
    </div>

</aside>