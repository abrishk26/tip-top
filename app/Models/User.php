<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasUlids, HasApiTokens, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'phone',
        'role',
        'is_active',
        'email_verified',
        'verification_token',
        'verification_token_expires_at',
        'email_verified_at',
        'last_login_at'
    ];

    protected $hidden = [
        'password_hash',
        'verification_token',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_token_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'email_verified' => 'boolean',
    ];

    // Helper methods for checking user status (no business logic)
    public function isVerified()
    {
        return $this->email_verified;
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }
}
