<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    //

    protected $guarded = [];
    protected $table = 'aset';
    protected $fillable = [
        'nama_aset',
        'kategori_aset',
        'lokasi_aset',
        'kondisi_aset',
        'nomor_serial',
        'tgl_pembelian',
        'nilai_perolehan',
        'status_aset',
        'keterangan',
        'qr_code',
        'foto',
        'departement_id',
        'user_id',
        'category_id'
    ];
public function tickets() {
        return $this->hasMany(Ticket::class); // Untuk melihat history servis
    }

    public function departement() {
        return $this->belongsTo(Departement::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function category() {
        return $this->belongsTo(Categories::class, 'category_id');
    }

}
