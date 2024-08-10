Here’s the updated README file with the addition of the `HasPermissionsTrait` to the User model:

---

## Zero Configuration Role-Permission Setup

<p align="center">
  <a href="https://paypal.me/teamdevgeek">
   Donate
  </a>
</p>

### Getting Started

```bash
composer require erag/laravel-role-permission
```

### Step 1: Add Trait to User Model

Before publishing the role-permission files, add the `HasPermissionsTrait` to your `User` model. This trait is essential for handling roles and permissions in your application.

```php
<?php

namespace App\Models;

use EragPermission\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasPermissionsTrait;

    // Your existing code...

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### Step 2: Automatic Database Configuration

After installation, your database will be automatically configured to run the necessary migrations and seeders without any additional setup.

### Step 3: Publish Role-Permission Files

Once the trait is added and the database is configured, publish the required migration and model files with a single command:

```bash
php artisan erag:publish-permission
```

This command will:

- Publish Role and Permission models.
- Publish and run the required migrations.
- Automatically run the seeder to set up roles and permissions in your database.

### Step 4: Register the Service Provider

#### For Laravel v11.x

Ensure the service provider is registered in your `/bootstrap/providers.php` file:

```php
return [
    // ...
    EragPermission\PermissionServiceProvider::class,
];
```

#### For Laravel v10.x

Ensure the service provider is registered in your `config/app.php` file:

```php
'providers' => [
    // ...
    EragPermission\PermissionServiceProvider::class,
],
```

### Step 5: Using Role-Based Permissions

You can now easily check user permissions within your application logic:

```php
if (auth()->user()->can('permission_name')) {
    // The user has the specified permission
}
```

### Step 6: Protecting Routes with Middleware

To protect routes based on roles and permissions, you can use the provided middleware. For example, to allow only users with the `user` role and `create-user` permission:

```php
Route::group(['middleware' => ['role:user,create-user']], function() {
    // Protected routes go here
});

Route::group(['middleware' => ['role:admin,create-post']], function() {
    // Protected routes go here
});

```

### Step 7: Displaying Content Based on Roles

You can also use Blade directives to display content based on the user's role:

```php
@role('admin')
    {{ __('You are admin') }}
@endrole

@role('user')
    {{ __('You are user') }}
@endrole
```

### Example Seeder for Roles and Permissions

Here's an example `RolePermissionSeeder` that seeds roles, permissions, and users:

```php
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
```

### License

The MIT License (MIT). Please see the License File for more information.

> GitHub [@eramitgupta](https://github.com/eramitgupta) &nbsp;&middot;&nbsp;
> Linkedin [@eramitgupta](https://www.linkedin.com/in/eramitgupta/)&nbsp;&middot;&nbsp;
> Donate [@eramitgupta](https://paypal.me/teamdevgeek/)

---

This README now includes a complete setup guide, usage examples, and a seeder for roles and permissions, ensuring that your application is ready to use the role-permission package.