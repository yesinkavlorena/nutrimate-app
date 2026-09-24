<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Nama tabel database.
     */
    protected $table = 'users';

    /**
     * Primary key.
     */
    protected $primaryKey = 'id';

    /**
     * ID bukan auto increment.
     */
    public $incrementing = false;

    /**
     * Tipe ID adalah string.
     */
    protected $keyType = 'string';

    /**
     * Kolom yang boleh diisi melalui create().
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
    ];

    /**
     * Kolom yang disembunyikan.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting data.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}