<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilPengguna extends Model
{
    protected $table = 'profil_pengguna';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'usia',
        'jenis_kelamin',
        'berat_badan',
        'tinggi_badan',
        'aktivitas_fisik',
    ];

    protected $casts = [
        'usia' => 'integer',
        'berat_badan' => 'float',
        'tinggi_badan' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
}