<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class VerificationToken extends Model
{
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

<<<<<<< HEAD
    protected $casts = [
        'expires_at' => 'datetime',
    ];
=======
    protected function casts() {
        return [
            "expires_at" => "datetime"
        ];
    }
>>>>>>> f5fe80b8a8c5c81fe0b035466ca8c70f397f9a6f
}
