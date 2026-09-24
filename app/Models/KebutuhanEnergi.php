<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KebutuhanEnergi extends Model
{
    protected $table = 'kebutuhan_energi';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'bmi',
        'kategori_bmi',
        'bmr',
        'tdee',
        'target_kalori_harian',
        'target_sarapan',
        'target_makan_siang',
        'target_makan_malam',
        'target_snack',
    ];

    protected $casts = [
        'bmi' => 'decimal:2',
        'bmr' => 'decimal:2',
        'tdee' => 'decimal:2',
        'target_kalori_harian' => 'decimal:2',
        'target_sarapan' => 'decimal:2',
        'target_makan_siang' => 'decimal:2',
        'target_makan_malam' => 'decimal:2',
        'target_snack' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}