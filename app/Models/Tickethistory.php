<?php

namespace App\Models;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;


use Illuminate\Database\Eloquent\Model;

class Tickethistory extends Model

{
    protected $table = 'ticket_histories';
    //

    use HasFactory;

    // Mass assignment protection
    protected $fillable = [
        'ticket_id',
        'user_id',
        'status_from',
        'status_to',
        'comment',
        'is_read',
        'received_id'
    ];

    /**
     * Relasi kembali ke Ticket.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relasi ke User yang melakukan perubahan (HRD/Teknisi/User).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper untuk memformat status agar lebih enak dibaca di UI.
     * Contoh: 'pending_hrd' menjadi 'Pending HRD'
     */
    public function formatStatus($status)
    {
        return str_replace('_', ' ', ucfirst($status));
    }
}
