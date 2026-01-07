<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\SoftDeletes;

class Pinjaman extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $table = 'pinjaman';

    protected $fillable = [
        'pengguna_id',
        'buku_id',
        'status',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'denda',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_kembali' => 'date',
        'denda' => 'float',
        'status' => 'string',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    // Relasi ke buku
    public function buku()
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }
}
