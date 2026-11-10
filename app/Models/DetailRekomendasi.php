<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailRekomendasi extends Model
{
    protected $table = 'detail_rekomendasi';

    protected $primaryKey = 'id_detail';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_detail',
        'id_rekomendasi',
        'id_makanan',
        'ranking',
        'skor_similarity',
        'macro_score',
        'hybrid_score'
    ];

    public function riwayatRekomendasi(): BelongsTo
    {
        return $this->belongsTo(
            RiwayatRekomendasi::class,
            'id_rekomendasi',
            'id_rekomendasi'
        );
    }

    public function makanan(): BelongsTo
    {
        return $this->belongsTo(
            Makanan::class,
            'id_makanan',
            'id_makanan'
        );
    }
}
