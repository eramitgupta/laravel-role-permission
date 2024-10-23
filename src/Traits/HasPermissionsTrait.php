<?php

namespace EragPermission\Traits;

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

    public function hasPermissions(string $permissions): bool
    {
        $arrayPermissions = array_map('trim', preg_split('/[,|]/', $permissions));
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
