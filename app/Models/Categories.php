<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    //
    protected $table = 'categories';
    protected $fillable = ['name'];

    public function aset()
    {
        return $this->hasMany(Aset::class, 'category_id');
    }


}
