<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class aktifitas extends Model
{
    protected $table = 'aktifitas'; // pastikan sama dengan nama tabel

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'deskripsi',
        'created_at',
        'updated_at',
    ];
}
