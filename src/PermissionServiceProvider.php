<?php

namespace EragPermission;

use EragPermission\Commands\NewUpdate;
use EragPermission\Commands\PublishPermissionMigrations;
use EragPermission\Contracts\PermissionContract;
use EragPermission\Contracts\RoleContract;
use EragPermission\Middleware\RolePermissionMiddleware;
use EragPermission\Models\Permission;
use EragPermission\Models\Role;
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
            NewUpdate::class,
        ]);

        $this->publishes([
            __DIR__.'/database/01_create_roles_table.php.stub' => database_path('migrations/0001_create_roles_table.php'),
            __DIR__.'/database/02_create_permissions_table.php.stub' => database_path('migrations/0002_create_permissions_table.php'),
            __DIR__.'/database/03_create_users_permissions_table.php.stub' => database_path('migrations/0003_create_users_permissions_table.php'),
            __DIR__.'/database/04_users_roles.php.stub' => database_path('migrations/0004_users_roles.php'),
            __DIR__.'/database/05_create_roles_permissions_table.php.stub' => database_path('migrations/0005_create_roles_permissions_table.php'),

        ], 'erag:publish-permission-migrations');

        $this->publishes([
            __DIR__.'/database/06_users_permissions_and_users_roles_add_column.php.stub' => database_path('migrations/06_users_permissions_and_users_roles_add_column.php'),
        ], 'erag:new-update');

        $this->publishes([
            __DIR__.'/database/Seeder/RolePermissionSeeder.php.stub' => database_path('seeders/RolePermissionSeeder.php'),
        ], 'erag:publish-permission-role-seeder');

        $this->ModelBindings();
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        require __DIR__.'/HelperRolePermission.php';

        $router->aliasMiddleware('role', RolePermissionMiddleware::class);
        $router->middlewareGroup('role', [RolePermissionMiddleware::class]);

        Permission::with('roles.users')->get()->each(function ($permission) {
            Gate::define($permission->name, function ($user) use ($permission) {
                return $user->hasPermissionTo($permission);
            });
        });

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

    protected function ModelBindings(): void
    {
        $this->app->bind(RoleContract::class, function ($app) {
            return new Role;
        });

        $this->app->bind(PermissionContract::class, function ($app) {
            return new Permission;
        });
    }
}
