<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubAccount extends Model
{
    use HasUlids, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    // Relationship to Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
