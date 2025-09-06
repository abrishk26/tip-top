<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeData extends Model
{
    use HasUlids, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'employees_data';

    protected $guarded = [];
    protected $hidden = ['password_hash'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
