@extends('layouts.app')

@section('title', 'Master Unit Kerja')

@section('content')
<div class="w-full px-6 py-6">
    
    {{-- 1. HEADER & STATS SUMMARY --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Unit Kerja</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data OPD, struktur organisasi, dan hierarki jabatan.</p>
        </div>
        
        {{-- Tombol Aksi Utama --}}
        <button onclick="openModal('add')" 
            class="group bg-[#1C7C54] hover:bg-[#166443] text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-700/20 transition-all duration-200 flex items-center gap-2 text-sm font-medium transform active:scale-95">
            <div class="bg-white/20 p-1 rounded-md group-hover:rotate-90 transition-transform">
                <i class="fas fa-plus fa-xs"></i>
            </div>
            Tambah Unit Baru
        </button>
    </div>

    {{-- 2. MAIN CARD --}}
    <div class="bg-white rounded-[24px] shadow-sm ring-1 ring-slate-200/60 overflow-hidden">
        {{-- ... konten tabel di bawah sini biarkan saja ... --}}
        
        {{-- Toolbar: Custom Search & Filters --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            
            {{-- Title Section --}}
            <div class="flex items-center gap-3">
                <div class="bg-emerald-100 text-emerald-600 w-10 h-10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h6 class="font-semibold text-slate-700">Daftar Unit Kerja</h6>
                    <span class="text-xs text-slate-400" id="total-records">Memuat data...</span>
                </div>
            </div>

            {{-- Custom Search Input (Lebih Modern) --}}
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input type="text" id="customSearch" 
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] sm:text-sm transition-shadow duration-200" 
                    placeholder="Cari nama unit kerja...">
            </div>
        </div>

        {{-- Table Content --}}
        <div class="relative">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tableUnit">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 border-b border-slate-100 w-[5%] text-center">No</th>
                            <th class="px-6 py-4 border-b border-slate-100">Nama Unit Kerja</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-center w-[15%]">Struktur</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-center w-[15%]">Personil</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-center w-[15%]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-600 divide-y divide-slate-100 bg-white">
                        {{-- Data via AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 3. MODAL COMPONENT (Optimized) --}}
<div id="modalUnit" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true">
    
    {{-- Backdrop dengan Blur --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity opacity-0" id="modalBackdrop"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            
            {{-- Modal Panel --}}
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-md opacity-0 scale-95" id="modalPanel">
                
                <form id="formUnit">
                    @csrf
                    <input type="hidden" id="unit_id" name="id">
                    <input type="hidden" name="_method" id="method" value="POST">

                    {{-- Modal Header --}}
                    <div class="bg-white px-6 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 z-10">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Tambah Unit Kerja</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Isi informasi unit kerja dengan benar.</p>
                        </div>
                        <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label for="nama_unit" class="block text-sm font-semibold text-slate-700 mb-2">
                                Nama Unit Kerja <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_unit" id="nama_unit" required
                                class="block w-full rounded-xl border-slate-300 py-3 px-4 text-slate-900 shadow-sm focus:border-[#1C7C54] focus:ring-[#1C7C54] sm:text-sm transition-all"
                                placeholder="Contoh: Badan Pendapatan Daerah">
                            <p class="mt-2 text-xs text-slate-400">Pastikan penulisan nama unit sesuai dengan nomenklatur resmi.</p>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                        <button type="submit" 
                            class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-[#1C7C54] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#166443] focus:outline-none focus:ring-2 focus:ring-[#1C7C54] focus:ring-offset-2 sm:w-auto transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save"></i> Simpan Data
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-all">
                            Batal
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let table;

    $(document).ready(function() {
        
        // 1. Inisialisasi DataTable dengan Custom Styling
        table = $('#tableUnit').DataTable({
            processing: true,
            serverSide: false, // Client side agar cepat untuk master data
            ajax: "{{ route('admin.master.unit-kerja.index') }}",
            // Sembunyikan search bawaan karena kita buat custom search
            dom: 'rt<"flex flex-col md:flex-row justify-between items-center gap-4 px-6 py-4 border-t border-slate-100"<"text-sm text-slate-500"i><"flex items-center gap-2"p>>',
            language: {
                zeroRecords: `<div class="py-8 flex flex-col items-center justify-center text-center">
                                <img src="{{ asset('assets/tips.svg') }}" class="h-24 w-24 mb-3 opacity-60" alt="No Data">
                                <p class="text-slate-500 font-medium">Belum ada data unit kerja.</p>
                                <p class="text-slate-400 text-sm">Silakan tambahkan data baru.</p>
                              </div>`,
                paginate: {
                    next: '<i class="fas fa-chevron-right"></i>',
                    previous: '<i class="fas fa-chevron-left"></i>'
                }
            },
            columns: [
                { 
                    data: null, 
                    render: (data, type, row, meta) => `<span class="font-medium text-slate-500">${meta.row + 1}</span>`, 
                    className: "px-6 py-4 align-middle text-center" 
                },
                { 
                    data: 'nama_unit', 
                    className: "px-6 py-4 align-middle font-semibold text-slate-700" 
                },
                { 
                    data: 'bidang_count', 
                    className: "px-6 py-4 align-middle text-center",
                    render: (data) => `<span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 border border-blue-100">
                                        <i class="fas fa-sitemap text-[10px]"></i> ${data} Bidang
                                      </span>`
                },
                { 
                    data: 'users_count', 
                    className: "px-6 py-4 align-middle text-center",
                    render: (data) => `<span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-100">
                                        <i class="fas fa-users text-[10px]"></i> ${data} Pegawai
                                      </span>`
                },
                {
                    data: 'id',
                    className: "px-6 py-4 align-middle text-center",
                    render: function(data, type, row) {
                        return `
                            <div class="flex justify-center gap-2">
                                <button class="group p-2 rounded-lg border border-transparent hover:border-amber-200 hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-all duration-200" 
                                    onclick='openModal("edit", ${JSON.stringify(row)})' title="Edit">
                                    <i class="fas fa-pen-to-square"></i>
                                </button>
                                <button class="group p-2 rounded-lg border border-transparent hover:border-red-200 hover:bg-red-50 text-slate-400 hover:text-red-600 transition-all duration-200" 
                                    onclick="deleteData(${data})" title="Hapus">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            drawCallback: function(settings) {
                // Update total records info di header
                let api = this.api();
                $('#total-records').text(`Total ${api.data().count()} Unit Terdaftar`);
                
                // Styling Pagination agar sesuai Tailwind
                $('.dataTables_paginate .paginate_button').addClass('px-3 py-1 mx-1 rounded-md text-sm hover:bg-slate-100 text-slate-600 transition cursor-pointer');
                $('.dataTables_paginate .paginate_button.current').addClass('bg-[#1C7C54] text-white hover:bg-[#166443] hover:text-white').removeClass('text-slate-600');
            }
        });

        // 2. Custom Search Logic (Debounced)
        let searchTimer;
        $('#customSearch').on('keyup', function() {
            clearTimeout(searchTimer);
            let value = this.value;
            searchTimer = setTimeout(function() {
                table.search(value).draw();
            }, 300); // Delay 300ms agar tidak berat
        });

        // 3. Handle Form Submit (AJAX)
        $('#formUnit').on('submit', function(e) {
            e.preventDefault();
            let id = $('#unit_id').val();
            let url = id ? `/admin/master/unit-kerja/${id}` : "{{ route('admin.master.unit-kerja.store') }}";
            let btn = $(this).find('button[type="submit"]');
            let originalContent = btn.html();

            // UI Loading State
            btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    closeModal();
                    table.ajax.reload(); // Refresh tabel
                    
                    // Notifikasi Sukses Custom
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: res.message
                    });
                },
                error: function(err) {
                    let msg = err.responseJSON.message || 'Terjadi kesalahan sistem.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: msg,
                        confirmButtonColor: '#1C7C54'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalContent);
                }
            });
        });
    });

    // 4. Modal Functions (Native JS + Tailwind)
    function openModal(type, data = null) {
        $('#formUnit')[0].reset();
        
        if (type === 'add') {
            $('#modalTitle').text('Tambah Unit Kerja Baru');
            $('#unit_id').val('');
            $('#method').val('POST');
        } else {
            $('#modalTitle').text('Perbarui Unit Kerja');
            $('#unit_id').val(data.id);
            $('#nama_unit').val(data.nama_unit);
            $('#method').val('PUT');
        }

        // Animasi Masuk
        $('#modalUnit').removeClass('hidden');
        requestAnimationFrame(() => {
            $('#modalBackdrop').removeClass('opacity-0');
            $('#modalPanel').removeClass('opacity-0 scale-95');
        });
    }

    function closeModal() {
        // Animasi Keluar
        $('#modalBackdrop').addClass('opacity-0');
        $('#modalPanel').addClass('opacity-0 scale-95');

        setTimeout(() => {
            $('#modalUnit').addClass('hidden');
        }, 200);
    }

    // 5. Delete Function (SweetAlert2 Styled)
    function deleteData(id) {
        Swal.fire({
            title: 'Hapus Unit ini?',
            text: "Data yang dihapus tidak bisa dikembalikan. Pastikan tidak ada pegawai terkait.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/master/unit-kerja/${id}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#1C7C54'
                        });
                        table.ajax.reload();
                    },
                    error: function(err) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: err.responseJSON.message,
                            icon: 'error',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush