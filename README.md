# Laravel Role-Permission

<center>
<img width="956" alt="Screenshot 2024-10-04 at 10 34 23 PM" src="https://github.com/user-attachments/assets/e78bffcf-6665-464b-a9a1-f6d8c72a9301">
</center>

<div align="center">

[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](https://github.com/eramitgupta/laravel-role-permission/blob/main/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/erag/laravel-role-permission?label=Stable)](https://packagist.org/packages/erag/laravel-role-permission)
[![php](https://img.shields.io/packagist/php-v/erag/laravel-role-permission.svg?color=purple)](https://packagist.org/packages/erag/laravel-role-permission)
[![Total Downloads](https://img.shields.io/packagist/dt/erag/laravel-role-permission.svg?label=Downloads)](https://packagist.org/packages/erag/laravel-role-permission)

</div>

## Getting Started

Install the package via Composer
```bash
composer require erag/laravel-role-permission
```

## Step 1: Add Trait to User Model

Before configuring the database and publishing the role-permission files, add the `HasPermissionsTrait` to define in your `User` model. This trait is essential for handling roles and permissions in your application.

```base
HasPermissionsTrait
```

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

## Step 3: Register the Service Provider

### For Laravel v11.x

Ensure the service provider is registered in your `/bootstrap/providers.php` file:

```php
return [
    // ...
    EragPermission\PermissionServiceProvider::class,
];
```

### For Laravel v9.x, v10.x

Ensure the service provider is registered in your `config/app.php` file:

```php
'providers' => [
    // ...
    EragPermission\PermissionServiceProvider::class,
],
```

## Step 4: Publish Role-Permission Files

Once the database is configured, publish the required migration with a single command:

```bash
php artisan erag:publish-permission
```

You can also run migrations and seeders:

```bash
php artisan erag:publish-permission --migrate
```
Or both

```bash
php artisan erag:publish-permission --migrate --seed
```

## Upgrade New Version Command
To upgrade the package to a new version:

```php
php artisan erag:upgrade-version 
```

## Available Functions Documentation

### Permission Management

#### `givePermissionsTo(array $permissions, string|array|null $expiresAt = null): static`
Assigns permissions to a user with optional expiration dates.
- `$permissions`: Array of permission names or objects
- `$expiresAt`: Can be null (no expiration), a string date, or array of dates mapped to permission names

```php
// Single expiration for all permissions
$user->givePermissionsTo(['post-create', 'post-edit'], Carbon::now()->addDays(30));

// Different expirations per permission
$user->givePermissionsTo(['post-create', 'post-edit'], [
    'post-create' => Carbon::now()->addDays(10),
    'post-edit' => Carbon::now()->addHours(6)
]);

// No expiration
$user->givePermissionsTo(['post-create']);
```

#### `detachPermissions(string|array $permissions): static`
Removes specified permissions from a user.
```php
// Multiple formats supported:
$user->detachPermissions(['post-create', 'post-edit']); // Array
$user->detachPermissions('post-create|post-edit');      // Pipe-separated
$user->detachPermissions('post-create,post-edit');      // Comma-separated
$user->detachPermissions('post-create');                // Single permission
```

#### `hasPermissionTo(...$permissions): bool`
Checks if user has specific permissions through roles or direct assignment.
```php
if ($user->hasPermissionTo('post-create', 'post-edit')) {
    // User has both permissions
}
```

#### `hasPermissions(string|array $permissions): bool`
Verifies user has all specified permissions.
```php
if ($user->hasPermissions(['post-create', 'post-edit'])) {
    // User has all permissions
}
```

### Role Management

#### `assignRole(string|array $roles): static`
Assigns roles to a user.
```php
$user->assignRole('admin');
$user->assignRole(['admin', 'editor']);
```

#### `hasRole(...$roles): bool`
Checks if user has any of the specified roles.
```php
if ($user->hasRole('admin', 'editor')) {
    // User has at least one of these roles
}
```

### Helper Methods

#### `hasPermissionThroughRole($permission): bool`
Checks if user has permission through assigned roles.

#### `roles()`
Relationship method for user roles.

#### `permissions()`
Relationship method for user permissions.

## Using Role-Based Permissions

You can now easily check user permissions within your application logic:
You can also use the helper method:
```php
if (hasPermissions('post-create')) {
    dd('You are allowed to access');
} else {
    dd('You are not allowed to access');
}
```
OR

```php
if (hasPermissions('post-create|post-edit')) {
    dd('You are allowed to access');
} else {
    dd('You are not allowed to access');
}

if (hasPermissions('post-create,post-edit')) {
    dd('You are allowed to access');
} else {
    dd('You are not allowed to access');
}
```

### Retrieve Permissions and Roles

```php
getPermissions();
```

```php
getRoles();
```

### Using Role-Based Checks

```php
if (hasRole('admin')) {
    dd('You are allowed to access');
} else {
    dd('You are not allowed to access');
}
```

## Blade Directives

```blade
@role('admin')
    <!-- Content for admins -->
@endrole

@hasPermissions('post-create')
    <!-- Content for users with post-create permission -->
@endhasPermissions

@hasPermissions('post-create|post-edit')
    <!-- Content for users with either permission -->
@endhasPermissions
```

## Middleware Usage


```php
Route::group(['middleware' => ['role:admin,post-create']], function () {
    // Routes protected by role and permissions
});

Route::group(['middleware' => ['permissions:post-create']], function () {
    // Routes protected by permissions
});

Route::post('/create-post', [PostController::class, 'create'])->name('post.create')->middleware('role:admin,post-create');
Route::post('/create-post', [PostController::class, 'create'])->name('post.create')->middleware('permissions:post-create');

```

## How to Use Permissions Expiration
The permission expiration feature allows you to set temporary access that expires automatically after a certain period or, by setting the expiration date to null, to allow unlimited access. This feature is useful for setting up both temporary and permanent permissions.

### Adding Permissions with Expiration

1. **Assign Permission with Expiration**: Use the `givePermissionsTo` method to assign a permission with an expiration date.

```php
// Assign a permission with a specific expiration date
$user->givePermissionsTo(['post-create', 'post-edit'], 
   Carbon::now()->addDays(30), // Each Permission expiration assign in 30 days
);
```

In this example, the `post-create` permission will be assigned to the user and expire after 30 days.

2. **Assign Multiple Permissions with Different Expirations**: If you need to assign multiple permissions with individual expiration dates, pass an associative array where the keys are permission names, and the values are the expiration dates.

```php
$user->givePermissionsTo(['post-create', 'post-edit'], 
[
  Carbon::now()->addDays(10), // Expires in 10 days
  Carbon::now()->addHours(6),   // Expires in 6 hours
]);
```

## How to Use without Permissions Expiration

1. **Assign Permission with Unlimited Duration**: Assign permissions without an expiration by setting the expiration to `null`. This will give the user unlimited access to the permission.

```php
// Assign a permission with a specific expiration date
$user->givePermissionsTo(['post-create'], 
   null, // [] Array or String 
);
```
OR

```php
$user->givePermissionsTo(['post-create', 'post-edit']);
```

## Example Seeder for Roles and Permissions

Here's an example `RolePermissionSeeder` that seeds roles, permissions, and users:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use EragPermission\Models\Permission;
use EragPermission\Models\Role;
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
            $this->seedRolePermissions();
            $this->seedUsers();
        });
    }

    /**
     * Seed role permissions.
     */
    private function seedRolePermissions(): void
    {
        $rolePermission = [
            'admin' => ['post-create', 'post-edit', 'post-delete', 'post-update'],
            'user' => ['user-create', 'user-edit', 'user-delete', 'user-update'],
        ];

        foreach ($rolePermission as $role => $permissions) {
            $role = Role::create(['name' => $role]);
            foreach ($permissions as $permission) {
                $permission = Permission::create(['name' => $permission]);
                $role->permissions()->attach($permission);
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
                'permissions' => [
                    'post-create' => Carbon::now()->addDays(30),
                    'post-edit' => Carbon::now()->addMinutes(60),
                ],
            ],
            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('user'),
                'roles' => ['user'],
                'permissions' => [
                    'user-create' => Carbon::now()->addDays(30),
                    'user-edit' => null,
                ],
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

            $user->assignRole($userData['roles']);
            $permissionsWithExpiry = $userData['permissions'];
            $user->givePermissionsTo(array_keys($permissionsWithExpiry), $permissionsWithExpiry);
            // $user->givePermissionsTo(array_keys($permissionsWithExpiry), Carbon::now()->addDays(30));
        }
    }
}
```

## Contribution 🧑‍💻

We welcome contributions to this project. Please read our [Contributing Guidelines](https://github.com/eramitgupta/laravel-role-permission/blob/main/CONTRIBUTING.md) before you start contributing.
