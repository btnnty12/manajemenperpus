<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    use HasFactory;

    protected $table = 'pesan';

    protected $fillable = [
        'pengirim_id',
        'penerima_id',
        'reply_to_id',
        'isi',
        'status',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pengirim()
    {
        return $this->belongsTo(Pengguna::class, 'pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsTo(Pengguna::class, 'penerima_id');
    }

    public function parent()
    {
        return $this->belongsTo(Pesan::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(Pesan::class, 'reply_to_id');
    }
}
