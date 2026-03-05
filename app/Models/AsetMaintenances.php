<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetMaintenances extends Model
{
    protected $fillable = [
        'aset_id', 'user_id', 'title', 'type',
        'description', 'maintenance_date', 'cost', 'attachment'
    ];

    // Relasi balik ke Aset
    public function aset() {
        return $this->belongsTo(Aset::class);
    }

    // Relasi ke User (Teknisi/Admin)
    public function user() {
        return $this->belongsTo(User::class);
    }
}
