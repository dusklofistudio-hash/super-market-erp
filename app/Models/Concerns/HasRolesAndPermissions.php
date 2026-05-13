<?php

namespace App\Models\Concerns;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Tiny, package-free RBAC mixin used by App\Models\User.
 *
 * The system avoids spatie/laravel-permission and similar packages — relations
 * are plain Eloquent BelongsToMany pivots backed by the migrations:
 *   user_role, role_permission, user_branch.
 */
trait HasRolesAndPermissions
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branch');
    }

    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $haystack = $this->roles->pluck('slug')->all();
        foreach ((array) $roles as $role) {
            if (in_array($role, $haystack, true)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_super_admin ?? false);
    }

    /** @return Collection<int,string> */
    public function getPermissionSlugs(): Collection
    {
        return $this->roles
            ->loadMissing('permissions')
            ->flatMap(fn (Role $role) => $role->permissions->pluck('slug'))
            ->unique()
            ->values();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->getPermissionSlugs()->contains($slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->getPermissionSlugs()->intersect($slugs)->isNotEmpty();
    }

    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    public function syncBranches(array $branchIds): void
    {
        $this->branches()->sync($branchIds);
    }
}
