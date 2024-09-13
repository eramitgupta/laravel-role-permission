<?php

namespace EragPermission\Traits;

use App\Models\Permission;
use App\Models\Role;

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
        foreach ($permissions as $permission) {
            if (! $this->hasPermissionThroughRole($permission) && ! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function hasPermissionThroughRole($permission): bool
    {
        foreach ($permission?->roles as $role) {
            if ($this->roles->contains($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasRole(...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->roles->contains('name', $role)) {
                return true;
            }
        }

        return false;
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
        return Permission::whereIn('name', $permissions)->get();
    }
}
