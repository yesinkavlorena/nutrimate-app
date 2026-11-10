<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiwayatRekomendasi extends Model
{
    protected $table = 'riwayat_rekomendasi';

    protected $primaryKey = 'id_rekomendasi';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Riwayat yang identik tidak disimpan lagi selama belum selama ini (jam).
     */
    public const JEDA_DUPLIKAT_JAM = 24;

    protected $fillable = [
        'id_rekomendasi',
        'user_id',
        'waktu_makan',
        'target_energi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function detailRekomendasi(): HasMany
    {
        return $this->hasMany(
            DetailRekomendasi::class,
            'id_rekomendasi',
            'id_rekomendasi'
        );
    }

    /**
     * Apakah pengguna sudah punya riwayat yang identik dalam
     * JEDA_DUPLIKAT_JAM jam terakhir?
     *
     * "Identik" berarti waktu makan sama, target energi sama, dan makanan
     * hasil rekomendasinya sama (termasuk urutan rankingnya).
     *
     * Jika model ini kelak memakai SoftDeletes, riwayat yang sudah
     * dihapus otomatis tidak ikut dihitung.
     */
    public static function sudahTersimpan(
        int|string $userId,
        string $waktuMakan,
        float $targetEnergi,
        array $idMakanan
    ): bool {
        $kandidat = static::with('detailRekomendasi')
            ->where('user_id', $userId)
            ->where('waktu_makan', $waktuMakan)
            ->where(
                'created_at',
                '>',
                now()->subHours(self::JEDA_DUPLIKAT_JAM)
            )
            ->get();

        foreach ($kandidat as $riwayat) {
            if (abs((float) $riwayat->target_energi - $targetEnergi) > 0.01) {
                continue;
            }

            $idLama = $riwayat->detailRekomendasi
                ->sortBy('ranking')
                ->pluck('id_makanan')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            if ($idLama === $idMakanan) {
                return true;
            }
        }

        return false;
    }
}