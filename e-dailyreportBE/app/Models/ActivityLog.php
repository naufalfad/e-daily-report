<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    protected $table = 'activity_logs';
    protected $guarded = [];
    
    // Memberi tahu Laravel bahwa kita TIDAK pakai kolom 'updated_at'
    const UPDATED_AT = null;
    
    // Memberi tahu Laravel nama kolom 'created_at' kustom kita adalah 'timestamp'
    const CREATED_AT = 'timestamp';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}