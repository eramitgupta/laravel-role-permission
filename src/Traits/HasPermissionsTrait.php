<?php

namespace EragPermission\Traits;

use Carbon\Carbon;
use EragPermission\Models\Permission;
use EragPermission\Models\Role;
use EragPermission\Services\CoreUtility;

trait HasPermissionsTrait
{
    public function givePermissionsTo(array $permissions, string|array|null $expiresAt = null): static
    {
        if (empty($expiresAt)) {
            $expiresAt = null;
        }

        $permissions = $this->getAllPermissions($permissions);
        if ($permissions->isEmpty()) {
            return $this;
        }

        $syncData = $permissions->mapWithKeys(function ($permission) use ($expiresAt) {
            if (is_array($expiresAt)) {
                $expiration = $expiresAt[$permission->name] ?? $expiresAt[$permission->id] ?? null;
            } else {
                $expiration = $expiresAt;
            }

            return [$permission->id => ['expires_at' => $expiration]];
        })->toArray();

        $this->permissions()->syncWithoutDetaching($syncData);

        return $this;
    }

    public function withdrawPermissionsTo(...$permissions): static
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);

        return $this;
    }

    public function assignRole(string|array $roles): static
    {
        $rolesArray = CoreUtility::stringArray($roles);
        $rolesCollection = $this->getAllRoles($rolesArray);

        if ($rolesCollection->isEmpty()) {
            return $this;
        }
        $this->roles()->syncWithoutDetaching($rolesCollection);

        return $this;
    }

    protected function getAllRoles(array $roles)
    {
        $roleNames = collect($roles)->map(function ($role) {
            return is_object($role) ? $role->name : $role;
        });

        return Role::whereIn('name', $roleNames)->get();
    }

    public function hasPermissionTo(...$arrayPermissions): bool
    {
        $permissions = $this->getAllPermissions($arrayPermissions);

        if ($permissions->isEmpty()) {
            return false;
        }

        return $permissions->every(function ($permission) {
            return $this->hasPermissionThroughRole($permission) && $this->hasPermission($permission);
        });
    }

    public function hasPermissions(string|array $permissions): bool
    {
        $arrayPermissions = CoreUtility::stringArray($permissions);
        foreach ($arrayPermissions as $permission) {
            if (! $this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    public function hasPermissionThroughRole($permission): bool
    {
        $this->load('roles');

        return $this->roles->pluck('id')->intersect($permission->roles->pluck('id'))->isNotEmpty();
    }

    public function hasRole(...$roles): bool
    {
        $userRoles = $this->roles->pluck('name')->toArray();

        return ! empty(array_intersect($roles, $userRoles));
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions')->withPivot('expires_at');
    }

    protected function hasPermission($permission): bool
    {
        $permissionRecord = $this->permissions()->where('name', $permission->name)->first();

        if (! $permissionRecord) {
            return false;
        }

        if ($permissionRecord->pivot->expires_at !== null) {
            return Carbon::now()->lt($permissionRecord->pivot->expires_at);
        }

        return true;
    }

    protected function getAllPermissions(array $permissions)
    {
        $permissionNames = array_map(fn ($permission) => is_object($permission) ? $permission->name : $permission, $permissions);

        return Permission::whereIn('name', $permissionNames)->with('roles')->get();
    }
}
