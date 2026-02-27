<?php

namespace App\Models;
use App\Models\User;
use App\Models\Department;
use App\Models\TicketHistory;


use Illuminate\Database\Eloquent\Model;


class Ticket extends Model
{
    protected $table = 'tickets';
    protected $fillable = [
        'ticket_number',
        'category',
        'tindakan',
        'resource_type',
        'problem_detail',
        'hrd_id',
        'technician_id',
        'emergency_action',
        'status',
        'user_id',
        'schedule_date',
        'action_plan',
        'target_departement_id',
        'damage_analysis',
        'temp_action',
        'perm_action',
        'preventive_action',
        'completion_date',
        'total_down_time_minutes',
        'result',
        'alloted_time',
        'attachment',
        'aset_id',
        'alasan_pembatalan',
        'kode_ppp'
    ];
protected $casts = [
    'completion_date' => 'datetime',
    'created_at' => 'datetime',
];
    protected $guarded = [];

    // Relasi ke Pembuat
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke HRD yang memproses
    public function hrd() {
        return $this->belongsTo(User::class, 'hrd_id');
    }

    // Relasi ke Divisi Tujuan (IT/Engineering)
    public function target_departement() {
        return $this->belongsTo(Departement::class, 'target_departement_id');
    }

    // Relasi ke Teknisi
    public function technician() {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // Helper untuk DaisyUI Badge Color
    public function getStatusColorAttribute() {
        return match($this->status) {
            'pending_hrd' => 'warning',
            'assigned'    => 'info',
            'in_progress' => 'primary',
            'completed'   => 'success',
            'rejected'    => 'error',
            default       => 'ghost',
        };
    }
    public function histories()
{
    return $this->hasMany(TicketHistory::class, 'ticket_id');
}

public function aset() {
        return $this->belongsTo(Aset::class);
    }
}
