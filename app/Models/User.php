<?php

namespace App\Models;

use App\Models\Concerns\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasRolesAndPermissions, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'phone', 'avatar',
        'default_branch_id', 'is_active', 'is_super_admin', 'locale',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function defaultBranch()
    {
        return $this->belongsTo(Branch::class, 'default_branch_id');
    }
}
