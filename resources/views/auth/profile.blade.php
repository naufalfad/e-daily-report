@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Profil & Akun Saya</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola data diri dan keamanan akun Anda di sini.</p>
    </div>

    {{-- NEW: Penanda untuk SweetAlert jika ada pesan sukses dari server (AKUN & LOGOUT) --}}
    @if(session('account_updated'))
    <div id="account-update-success" data-username="{{ session('account_updated') }}" class="hidden"></div>
    @endif

    {{-- Penanda untuk sukses update Biodata (TANPA LOGOUT) --}}
    @if(session('biodata_updated'))
    <div id="biodata-update-success" data-message="{{ session('biodata_updated') }}" class="hidden"></div>
    @endif

    @if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative shadow-sm">
        <strong class="font-bold flex items-center"><i class="fas fa-times-circle mr-2"></i> Terjadi Kesalahan:</strong>
        <ul class="list-disc list-inside mt-1 ml-6 text-sm">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-1">
            <div
                class="bg-white rounded-[20px] shadow-[0_4px_20px_rgba(0,0,0,0.05)] overflow-hidden border border-gray-100">
                <div class="bg-[#1C7C54] px-6 py-4 flex justify-between items-center">
                    <h6 class="font-bold text-white text-lg">Data Kepegawaian</h6>
                    <span
                        class="bg-white/20 text-white text-xs px-2 py-1 rounded-full uppercase tracking-wide border border-white/20">
                        {{ $user->roles->pluck('nama_role')->first() ?? 'User' }}
                    </span>
                </div>

                <div class="p-6 text-center">
                    <div class="mb-5 flex justify-center">
                        <div class="relative w-[140px] h-[140px]">
                            <img class="w-full h-full rounded-full object-cover border-4 border-white shadow-lg"
                                src="{{ $user->foto_profil ? asset('storage/'.$user->foto_profil) : asset('assets/icon/avatar.png') }}"
                                alt="Foto Profil">
                        </div>
                    </div>

                    <h5 class="text-xl font-bold text-gray-800 mb-1">{{ $user->name }}</h5>
                    <p class="text-gray-500 text-sm mb-4 font-mono bg-gray-100 inline-block px-2 py-1 rounded">
                        {{ $user->nip ?? '-' }}</p>

                    <div class="border-t border-gray-100 my-4"></div>

                    <div class="text-left space-y-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jabatan</label>
                            <div
                                class="text-gray-800 font-medium bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 flex items-center">
                                <i class="fas fa-briefcase text-gray-300 mr-2"></i>
                                {{ $user->jabatan->nama_jabatan ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Unit
                                Kerja</label>
                            <div
                                class="text-gray-800 text-sm bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 flex items-center">
                                <i class="fas fa-building text-gray-300 mr-2"></i>
                                {{ $user->unitKerja->nama_unit ?? 'Non-Unit' }}
                            </div>
                        </div>

                        @if($user->atasan)
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 relative overflow-hidden">
                            <i
                                class="fas fa-user-tie absolute -right-3 -bottom-3 text-6xl text-blue-100 opacity-50 transform rotate-12"></i>

                            <label
                                class="flex items-center text-xs font-bold text-blue-600 uppercase tracking-wider mb-2 relative z-10">
                                <i class="fas fa-user-check mr-2"></i> Atasan Langsung
                            </label>
                            <div class="text-gray-800 font-bold text-sm relative z-10">{{ $user->atasan->name }}</div>
                            <small
                                class="text-gray-500 text-xs relative z-10">{{ $user->atasan->jabatan->nama_jabatan ?? '' }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div
                class="bg-white rounded-[20px] shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-gray-100 overflow-hidden min-h-[500px]">

                @php
                $hasAccountErrors = $errors->has('username') || $errors->has('password');
                $activeTab = $hasAccountErrors ? 'account' : 'biodata';
                @endphp

                <div class="flex border-b border-gray-200" id="tabs-nav">
                    <button onclick="switchTab('biodata')" id="tab-btn-biodata"
                        class="flex-1 py-4 text-center font-medium text-sm transition-all {{ $activeTab == 'biodata' ? 'border-b-2 border-[#1C7C54] text-[#1C7C54] bg-green-50/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-id-card mr-2"></i> Biodata Diri
                    </button>
                    <button onclick="switchTab('account')" id="tab-btn-account"
                        class="flex-1 py-4 text-center font-medium text-sm transition-all {{ $activeTab == 'account' ? 'border-b-2 border-[#1C7C54] text-[#1C7C54] bg-green-50/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-user-lock mr-2"></i> Akun & Keamanan
                        @if($hasAccountErrors)
                        <span
                            class="ml-2 bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full font-bold animate-pulse">!</span>
                        @endif
                    </button>
                </div>

                <div class="p-6 md:p-8">

                    <div id="tab-content-biodata" class="{{ $activeTab == 'biodata' ? 'block' : 'hidden' }}">
                        <form action="{{ route('profil.update-biodata') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Ganti Foto Profil</label>
                                <div class="flex items-center justify-center w-full">
                                    <label for="foto_profil"
                                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition group">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <i
                                                class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2 group-hover:text-[#1C7C54] transition"></i>
                                            <p class="text-sm text-gray-500 group-hover:text-gray-700"><span
                                                    class="font-semibold">Klik untuk upload</span></p>
                                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP (Max 2MB)</p>
                                        </div>
                                        <input id="foto_profil" name="foto_profil" type="file" class="hidden"
                                            accept="image/*" />
                                    </label>
                                </div>
                                <div id="file-name-display"
                                    class="mt-2 text-sm text-[#1C7C54] font-medium hidden flex items-center">
                                    <i class="fas fa-check mr-1"></i> <span></span>
                                </div>
                                @error('foto_profil')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">No. WhatsApp</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fab fa-whatsapp text-green-500 text-lg"></i>
                                        </div>
                                        <input type="tel" name="no_telp" value="{{ old('no_telp', $user->no_telp) }}"
                                            class="w-full pl-10 px-4 py-2 border border-gray-300 rounded-xl focus:ring-[#1C7C54] focus:border-[#1C7C54] transition"
                                            placeholder="08xxxx" inputmode="numeric" pattern="[0-9]*" autocomplete="tel"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Hanya angka.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                        </div>
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                            class="w-full pl-10 px-4 py-2 border border-gray-300 rounded-xl focus:ring-[#1C7C54] focus:border-[#1C7C54] transition"
                                            autocomplete="email">
                                    </div>
                                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mb-8">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Domisili</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                    <textarea name="alamat" rows="3"
                                        class="w-full pl-10 px-4 py-2 border border-gray-300 rounded-xl focus:ring-[#1C7C54] focus:border-[#1C7C54] transition"
                                        autocomplete="street-address">{{ old('alamat', $user->alamat) }}</textarea>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit"
                                    class="bg-[#1C7C54] hover:bg-[#156343] text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-green-900/10 transition-all transform hover:-translate-y-1 flex items-center justify-center ml-auto">
                                    <i class="fas fa-save mr-2"></i> Simpan Biodata
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="tab-content-account" class="{{ $activeTab == 'account' ? 'block' : 'hidden' }}">
                        <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-8 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-orange-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-orange-700">
                                        <strong>Penting:</strong> Mengubah Username atau Password akan mempengaruhi cara
                                        Anda login.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('profil.update-account') }}" method="POST" id="form-account">
                            @csrf
                            @method('PUT')

                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Username Login</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                        required
                                        class="w-full pl-10 px-4 py-2 border border-gray-300 rounded-xl focus:ring-orange-500 focus:border-orange-500 transition @error('username') border-red-500 @enderror"
                                        autocomplete="username">
                                </div>
                                @error('username')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-gray-400 text-xs mt-2 flex items-center">
                                    <i class="fas fa-info-circle mr-1"></i> Username default adalah NIP Anda.
                                </p>
                            </div>

                            <div class="border-t border-gray-100 my-8 relative">
                                <span
                                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-2 text-gray-400 text-sm">Keamanan</span>
                            </div>

                            <h6 class="text-red-600 font-bold mb-4 flex items-center">
                                <i class="fas fa-key mr-2"></i> Ganti Password (Opsional)
                            </h6>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                    <div class="relative w-full">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>

                                        <input type="password" id="password" name="password"
                                            placeholder="Kosongkan jika tidak ganti"
                                            class="w-full pl-10 pr-10 px-4 py-2 border border-gray-300 rounded-xl focus:ring-red-500 focus:border-red-500 transition @error('password') border-red-500 @enderror"
                                            autocomplete="new-password">

                                        <button type="button" onclick="togglePassword('password')"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-20 cursor-pointer h-full w-10">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ulangi Password</label>
                                    <div class="relative w-full">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>

                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                            placeholder="Ketik ulang password"
                                            class="w-full pl-10 pr-10 px-4 py-2 border border-gray-300 rounded-xl focus:ring-red-500 focus:border-red-500 transition">

                                        <button type="button" onclick="togglePassword('password_confirmation')"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-20 cursor-pointer h-full w-10">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="button" id="btn-trigger-modal"
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-red-900/10 transition-all transform hover:-translate-y-1 flex items-center justify-center ml-auto">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan Akun
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="tailwind-modal" class="hidden fixed inset-0 z-[999] overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div
            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border-t-4 border-red-500">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-key text-red-600 text-lg"></i>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Konfirmasi Perubahan
                            Login</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Anda sedang melakukan perubahan pada <strong>Akses Login (Username/Password)</strong>.
                                <br><br>
                                Mohon pastikan Anda mengingat data baru ini agar tidak terkunci dari sistem pada login
                                berikutnya.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" id="btn-confirm-final"
                    class="inline-flex w-full justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition transform hover:scale-105">
                    Ya, Simpan
                </button>
                <button type="button" id="btn-cancel-modal"
                    class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/profile-modal.js'])
@endpush

@endsection