<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Start a transaction to ensure data integrity
        DB::transaction(function () {
            $this->seedPermissions();
            $this->seedRoles();
            $this->seedUsers();
        });
    }

    /**
     * Seed permissions.
     */
    private function seedPermissions(): void
    {
        $permissions = [
            'create-post',
            'create-user',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }
    }

    /**
     * Seed roles and their permissions.
     */
    private function seedRoles(): void
    {
        $roles = [
            'admin' => ['create-post'],
            'user' => ['create-user'],
        ];

        foreach ($roles as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            foreach ($permissionNames as $permissionName) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                $role->permissions()->syncWithoutDetaching($permission);
                $permission->roles()->syncWithoutDetaching($role);
            }
        }
    }

    /**
     * Seed users and assign roles and permissions.
     */
    private function seedUsers(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin'),
                'roles' => ['admin'],
                'permissions' => ['create-post'],
            ],
            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('user'),
                'roles' => ['user'],
                'permissions' => ['create-user'],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]
            );

            foreach ($userData['roles'] as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->syncWithoutDetaching($role);
                }
            }

            foreach ($userData['permissions'] as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $user->permissions()->syncWithoutDetaching($permission);
                }
            }
        }
    }
}
