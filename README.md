# Laravel Role-Permission


<center>
<img width="956" alt="Screenshot 2024-10-04 at 10 34 23 PM" src="https://github.com/user-attachments/assets/e78bffcf-6665-464b-a9a1-f6d8c72a9301">
</center>

<div align="center">

[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](https://github.com/eramitgupta/laravel-role-permission/blob/main/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/erag/laravel-role-permission?label=Stable)](https://packagist.org/packages/erag/laravel-role-permission)
[![Total Downloads](https://img.shields.io/packagist/dt/erag/laravel-role-permission.svg?label=Downloads)](https://packagist.org/packages/erag/laravel-role-permission)

</div>


This package provides an effortless way to manage roles and permissions in your Laravel application. With automatic database configuration, one-command publishing, and easy integration, you can quickly set up robust role-based access control without hassle.

## Getting Started

```bash
composer require erag/laravel-role-permission
```

## Step 1: Add Trait to User Model

Before configuring the database and publishing the role-permission files, add the `HasPermissionsTrait` to define in your `User` model. This trait is essential for handling roles and permissions in your application.

```php
<?php

namespace App\Models;

use EragPermission\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasPermissionsTrait, Notifiable;

}
```

## Step 2: Database Configuration

Before proceeding with the setup, ensure that your database connection is properly configured in your `.env` file. Example configuration:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Make sure to replace `your_database_name`, `your_database_user`, and `your_database_password` with your actual database credentials.

## Step 3: Automatic Database Setup

After configuring your database connection, the package will automatically set up your database by running the necessary migrations and seeders without any additional setup.

## Step 4: Register the Service Provider

### For Laravel v11.x

Ensure the service provider is registered in your `/bootstrap/providers.php` file:

```php
return [
    // ...
    EragPermission\PermissionServiceProvider::class,
];
```

### For Laravel v10.x

Ensure the service provider is registered in your `config/app.php` file:

```php
'providers' => [
    // ...
    EragPermission\PermissionServiceProvider::class,
],
```

## Step 5: Publish Role-Permission Files

Once the database is configured, publish the required migration and model files with a single command:

```bash
php artisan erag:publish-permission
```

This command will:

- Publish and run the required migrations.
- Automatically run the seeder to set up roles and permissions in your database.

## Step 6: Using Role-Based Permissions

You can now easily check user permissions within your application logic:

```php
if (auth()->user()->can('permission_name')) {
    // The user has the specified permission
}
```

You can also use the helper method:

```php
if (hasPermissions('create-post')) {
    dd('You are allowed to access');
} else {
    dd('You are not allowed to access');
}
```

OR

```php
if (hasPermissions('create-post', 'post-edit')) {
    dd('You are allowed to access');
} else {
    dd('You are not allowed to access');
}
```

To get all permissions:

```php
getPermissions();
```

### Using Role-Based Checks

```php
if (hasRole('admin')) {
    dd('You are allowed to access');
} else {
    dd('You are not allowed to access');
}
```

To get all roles:

```php
getRoles();
```

## Step 7: Protecting Routes with Middleware

To protect routes based on roles and permissions, you can use the provided middleware. For example, to allow only users with the `user` role and `create-user` permission:

```php
Route::group(['middleware' => ['role:user,create-user']], function () {
    // Protected routes go here
});

Route::group(['middleware' => ['role:admin,create-post']], function () {
    // Protected routes go here
});
```

## Step 8: Displaying Content Based on Roles

You can also use Blade directives to display content based on the user's role:

```php
@role('admin')
    {{ __('You are an admin') }}
@endrole

@role('user')
    {{ __('You are a user') }}
@endrole
```

## Step 9: Displaying Content Based on Permissions

You can also use Blade directives to display content based on the user's permissions:

```php
@permission('create-post')
    {{ __('You can create a post') }}
@endpermission
```

## Example Seeder for Roles and Permissions

Here's an example `RolePermissionSeeder` that seeds roles, permissions, and users:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use EragPermission\Models\Role;
use EragPermission\Models\Permission;
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
        DB::transaction(function () {
            $this->seedPermissions();
            $this->seedRoles();
            $this->seedUsers();
        });
    }

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

    private function seedRoles(): void
    {
        $roles = [
            'admin' => ['create-post', 'post-edit', 'post-delete', 'post-update'],
            'user' => ['create-user', 'user-edit', 'user-delete', 'user-update'],
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
```

## Contribution 🧑‍💻

We welcome contributions to this project. Please read our [Contributing Guidelines](https://github.com/eramitgupta/laravel-role-permission/blob/main/CONTRIBUTING.md) before you start contributing.
