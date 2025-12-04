<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkpTarget extends Model
{
    use HasFactory;

    protected $table = 'skp_target';

    protected $fillable = [
        'skp_rencana_id',
        'jenis_aspek', // 'Kuantitas', 'Kualitas', 'Waktu', 'Biaya'
        'indikator',   // "Jumlah Dokumen"
        'target',      // 10
        'satuan',      // "Dokumen"
    ];

    /**
     * Relasi balik ke Rencana Induk
     */
    public function rencana(): BelongsTo
    {
        return $this->belongsTo(SkpRencana::class, 'skp_rencana_id');
    }
}