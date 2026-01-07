<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'email',
        'kata_sandi',
        'peran',
        'foto',
        'phone',
    ];

    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    // Method untuk mendapatkan URL foto profil
    public function getFotoUrlAttribute()
    {
        $foto = $this->attributes['foto'] ?? null;
        if ($foto) {
            // Pastikan path sudah benar
            if (file_exists(storage_path('app/public/' . $foto))) {
                return asset('storage/' . $foto);
            }
        }
        return null;
    }
    
    // Accessor langsung untuk foto
    public function getFotoAttribute($value)
    {
        return $value;
    }

    // Accessor untuk profile_photo (kompatibilitas dengan view yang sudah ada)
    public function getProfilePhotoAttribute()
    {
        $foto = $this->attributes['foto'] ?? null;
        if ($foto) {
            // Return URL jika file exists
            if (file_exists(storage_path('app/public/' . $foto))) {
                return asset('storage/' . $foto);
            }
        }
        return null;
    }

    /**
     * Gunakan kolom kata_sandi untuk autentikasi
     * Laravel Auth::attempt() akan memanggil method ini untuk mendapatkan password dari database
     */
    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    /**
     * Mutator hash password otomatis
     * Hanya hash jika value belum ter-hash (untuk menghindari double hashing)
     */
    public function setKataSandiAttribute($value)
    {
        // Hanya hash jika value belum ter-hash (panjang hash bcrypt biasanya 60 karakter)
        if (! empty($value) && strlen($value) < 60) {
            $this->attributes['kata_sandi'] = bcrypt($value);
        } else {
            $this->attributes['kata_sandi'] = $value;
        }
    }

    // RELASI
    public function pinjaman()
    {
        return $this->hasMany(Pinjaman::class, 'pengguna_id');
    }
    
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Buku::class, 'favorites', 'user_id', 'book_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'pengguna_id');
    }
    // HELPER ROLE
    public function isAdmin()
    {
        return $this->peran === 'admin';
    }

    public function isStaff()
    {
        return $this->peran === 'staff';
    }

    public function isPengguna()
    {
        return $this->peran === 'pengguna';
    }
}
