<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreferensiMakanan extends Model
{
    protected $table = 'preferensi_makanan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'makanan_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function makanan()
    {
        return $this->belongsTo(
            Makanan::class,
            'makanan_id',
            'id_makanan'
        );
    }
}