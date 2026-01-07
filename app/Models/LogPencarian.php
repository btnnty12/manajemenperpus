<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPencarian extends Model
{
    use HasFactory;

    protected $table = 'log_pencarian';

    protected $fillable = [
        'pengguna_id',
        'kata_kunci',
        'jumlah_hasil',
        'algorithm',
        'process_time_ms',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }
}
