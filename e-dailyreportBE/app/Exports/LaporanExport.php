<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    // Constructor menerima Collection Data langsung
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Pegawai',
            'Waktu',
            'Jenis Kegiatan',
            'Deskripsi Aktivitas',
            'Output/Hasil',
            'Status SKP',
            'Status Validasi',
        ];
    }

    public function map($lkh): array
    {
        return [
            $lkh->tanggal_laporan,
            $lkh->user->name ?? '-',
            $lkh->waktu_mulai . ' - ' . $lkh->waktu_selesai,
            $lkh->jenis_kegiatan,
            $lkh->deskripsi_aktivitas,
            $lkh->output_hasil_kerja,
            $lkh->skp ? $lkh->skp->nama_skp : 'Non-SKP',
            strtoupper($lkh->status),
        ];
    }
}