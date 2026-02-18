<?php

namespace App\Models;
use App\Models\User;
use App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $table = 'departements';
    protected $fillable = ['name', 'code', 'description'];


   public function users() {
        return $this->hasMany(User::class);
    }

    // Tiket yang masuk ke divisi ini
    public function incoming_tickets() {
        return $this->hasMany(Ticket::class, 'target_departement_id');
    }
}
