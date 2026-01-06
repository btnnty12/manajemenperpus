<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'pengguna_id',
        'judul',
        'pesan',
        'tipe',
        'dibaca',
        'link',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    // Scope untuk notifikasi yang belum dibaca
    public function scopeBelumDibaca($query)
    {
        return $query->where('dibaca', false);
    }

    // Scope untuk notifikasi berdasarkan tipe
    public function scopeByTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }
}
