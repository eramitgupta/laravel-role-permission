<?php

namespace EragPermission\Traits;

use Carbon\Carbon;
use EragPermission\Models\Permission;
use EragPermission\Models\Role;

trait HasPermissionsTrait
{
    public function givePermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        if ($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);

        return $this;
    }

    public function withdrawPermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);

        return $this;
    }

    public function refreshPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionsTo($permissions);
    }

    public function hasPermissionTo(...$permissions): bool
    {
        $permissions = $this->getAllPermissions($permissions);

        return $permissions->isNotEmpty() && $permissions->every(function ($permission) {
            return $this->hasPermissionThroughRole($permission) && $this->hasValidPermission($permission);
        });
    }

    public function hasPermissions(string $permissions): bool
    {
        $permissions = preg_split('/[,|]/', $permissions);
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (! $this->hasPermissionTo($permission)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function hasPermissionThroughRole($permission): bool
    {
        $this->load('roles');

        return $this->roles->pluck('id')->intersect($permission->roles->pluck('id'))->isNotEmpty();
    }

    public function hasRole(...$roles): bool
    {
        $userRoles = $this->roles->filter(function ($role) {
            return $this->hasValidRole($role);
        })->pluck('name')->toArray();

        return empty(array_diff($roles, $userRoles));
    }

    protected function hasValidPermission($permission): bool
    {
        if ($permission->expires_at === null) {
            return true;
        }

        return Carbon::now()->lt($permission->expires_at);
    }

    protected function hasValidRole($role): bool
    {
        if ($role->expires_at === null) {
            return true;
        }

        return Carbon::now()->lt($role->expires_at);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }

    protected function hasPermission($permission): bool
    {
        return (bool) $this->permissions->where('name', $permission->name)->count();
    }

    protected function getAllPermissions(array $permissions)
    {
        $permissionNames = array_map(fn ($permission) => is_object($permission) ? $permission->name : $permission, $permissions);
        return Permission::whereIn('name', $permissionNames)->with('roles')->get();
    }
}
