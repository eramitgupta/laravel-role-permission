# Laravel Role-Permission

This package provides an effortless way to manage roles and permissions in your Laravel application. With automatic database configuration, one-command publishing, and easy integration, you can quickly set up robust role-based access control without hassle.

## Getting Started

```bash
composer require erag/laravel-role-permission
```

## Step 1: Add Trait to User Model & Define Relationships

Before configuring the database and publishing the role-permission files, add the `HasPermissionsTrait` and `roles` to define relationships in your `User` model. This trait is essential for handling roles and permissions in your application.

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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }
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

- Publish Role and Permission models.
- Publish and run the required migrations.
- Automatically run the seeder to set up roles and permissions in your database.

## Step 6: Using Role-Based Permissions

You can now easily check user permissions within your application logic:

```php
if (auth()->user()->can('permission_name')) {
    // The user has the specified permission
}
```

OR

```php
if (auth()->user()->can('permission_name_1', 'permission_name_2')) {
    // The user has one of the specified permissions
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

## Example Seeder for Roles and Permissions

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

We appreciate your interest in contributing to this Laravel Roles and Permissions project! Whether you're reporting issues, fixing bugs, or adding new features, your help is greatly appreciated.

## Forking and Cloning the Repository

### Fork the Repository

1. Go to the repository page on GitHub.
2. Click the **Fork** button at the top-right corner of the repository page.

### Clone the Repository

Once you've forked the repository:

1. Open your terminal or Git Bash.
2. Clone the repository to your local machine:

   ```bash
   git clone https://github.com/your-username/example-app.git
   ```

## Reporting Issues

If you encounter any issues or bugs, please check if the issue already exists in the **Issues** section of the repository. If not, create a new issue and provide as much detail as possible, including:

- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Laravel version
- Any relevant logs or screenshots

## Submit a Pull Request

When you're ready to submit your changes, go to the repository on GitHub and open a new **Pull Request**. Describe the changes you've made and how they address the issue or add new functionality.

## Submitting Changes

All pull requests will undergo a review process to ensure the changes adhere to the project standards and do not introduce any bugs.

## Squashing Commits

We prefer that all commits be squashed into a single commit per pull request. This helps keep the project history clean.

## Coding Standards

Please adhere to the following coding standards to ensure consistency across the codebase:

- **PSR-12**: Follow the [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/) for PHP.
- **Comments**: Write clear and concise comments where necessary. Avoid over-commenting but provide clarity for complex logic.

## Contributing Guidelines

- Ensure your pull requests are made from a feature branch (`feature/name-of-feature`).
- Document your changes
- Use a clean and meaningful commit history.

We appreciate your efforts in contributing to this project! For any further questions, feel free to reach out via GitHub.

Happy coding 🧑‍💻!
