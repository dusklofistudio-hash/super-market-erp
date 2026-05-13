<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['module', 'name', 'slug', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    public static function check(string $slug): bool
    {
        $user = auth()->user();

        return $user && $user->hasPermission($slug);
    }
}
