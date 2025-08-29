<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Model
{
    use HasUlids, HasApiTokens;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
