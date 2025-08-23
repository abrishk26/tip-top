<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Transaction extends Model
{
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];
}
