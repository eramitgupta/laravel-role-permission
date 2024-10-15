<?php

namespace EragPermission;

use App\Models\Permission;
use EragPermission\Commands\PublishPermissionMigrations;
use EragPermission\Middleware\RolePermissionMiddleware;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([
            PublishPermissionMigrations::class,
        ]);

        $this->publishes([
            __DIR__.'/Models/Role.php.stub' => app_path('Models/Role.php'),
            __DIR__.'/Models/Permission.php.stub' => app_path('Models/Permission.php'),
        ], 'erag:publish-permission-models');

        $this->publishes([
            __DIR__.'/database/01_create_roles_table.php.stub' => database_path('migrations/0001_create_roles_table.php'),
            __DIR__.'/database/02_create_permissions_table.php.stub' => database_path('migrations/0002_create_permissions_table.php'),
            __DIR__.'/database/03_create_users_permissions_table.php.stub' => database_path('migrations/0003_create_users_permissions_table.php'),
            __DIR__.'/database/04_users_roles.php.stub' => database_path('migrations/0004_users_roles.php'),
            __DIR__.'/database/05_create_roles_permissions_table.php.stub' => database_path('migrations/0005_create_roles_permissions_table.php'),

        ], 'erag:publish-permission-migrations');

        $this->publishes([
            __DIR__.'/database/Seeder/RolePermissionSeeder.php.stub' => database_path('seeders/RolePermissionSeeder.php'),
        ], 'erag:publish-permission-role-seeder');
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        require __DIR__.'/HelperRolePermission.php';

        // Register middleware alias
        $router->aliasMiddleware('role', RolePermissionMiddleware::class);
        $router->middlewareGroup('role', [RolePermissionMiddleware::class]);

        try {
            if (file_exists(app_path('Models/Permission.php'))) {
                Permission::with('roles.users')->get()->each(function ($permission) {
                    Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermissionTo($permission);
                    });
                });
            }
        } catch (\Exception $e) {
            report($e);
        }

        Blade::directive('role', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})) : ?>";
        });

        Blade::directive('endrole', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('permission', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->can({$permission})) : ?>";
        });

        Blade::directive('endpermission', function () {
            return '<?php endif; ?>';
        });
    }
}
