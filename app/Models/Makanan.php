<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Makanan extends Model
{
    protected $table = 'makanan';

    protected $primaryKey = 'id_makanan';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_makanan',
        'nama_makanan',
        'kalori',
        'protein',
        'lemak',
        'karbohidrat',
        'waktu_makan',
        'gambar_makanan',
        'berkuah',
        'digoreng',
        'dibakar',
        'dipanggang',
        'direbus',
        'dikukus',
        'ditumis',
        'pedas',
        'manis',
        'gurih',
        'asam',
        'bersantan',
        'berbumbu_rempah',
        'bertepung',
        'berbahan_ayam',
        'berbahan_sapi',
        'berbahan_kambing',
        'berbahan_ikan',
        'berbahan_seafood',
        'berbahsn_bebek',
        'berbahan_telur',
        'berbahan_sayur',
        'berbahan_tahu_tempe',
        'berbahan_dasar_tepung',
        'berbumbu_kacang',
        'berbumbu_balado',
        'berbumbu_bali',
    ];
}