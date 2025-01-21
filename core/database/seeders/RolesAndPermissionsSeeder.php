<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $admin_actions = [
            'dropdown',
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ];

        $admin_modules = [
            'bookings',
            'customers',
            //'providers',
            'transactions',
            //'schedule',
            //'services',
            //'pricingModels',
            'setting',
            //'branches',
            'users',
            //'expense_categories',
            //'expenses',
            //'product_categories',
            //'products',
            //'procurements',
            //'adjustments',
        ];

        $admin_permissions = collect($admin_modules)->flatMap(function ($module) use ($admin_actions) {
            return collect($admin_actions)->map(function ($action) use ($module) {
                return "{$module}.{$action}";
            });
        })->toArray();

        $assistant_actions = [
            'dropdown',
            'index',
            'store',
            'show',
            'update',
        ];

        $assistant_modules = [
            'bookings',
            'customers',
            //'providers',
            'transactions',
            //'expenses',
            //'product_categories',
            //'products',
            //'procurements',
            //'adjustments',
        ];

        $assistant_permissions = collect($assistant_modules)->flatMap(function ($module) use ($assistant_actions) {
            return collect($assistant_actions)->map(function ($action) use ($module) {
                return  "{$module}.{$action}";
            });
        })->toArray();

        $assistant_permissions[] = 'services.dropdown';
        $assistant_permissions[] = 'setting.index';
        $assistant_permissions[] = 'schedule.index';
        $assistant_permissions[] = 'expenses.index';
        $assistant_permissions[] = 'expenses.store';

        $permissionsByRole = [
            'admin' => $admin_permissions,
            'assistant' => $assistant_permissions,
            //'provider' => [],
            'customer' => [],
        ];

        foreach ($permissionsByRole as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);
            foreach ($permissions as $permissionName) {
                $permission = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'api',
                ]);
                $role->givePermissionTo($permission);
            }
        }

        // Assign roles to users
        $this->assignRoleToUsers('admin', function ($query) {
            $query->whereNull('gender');
        });

        /* $this->assignRoleToUsers('provider', function ($query) {
            $query->whereHas('provider');
        }); */

        $this->assignRoleToUsers('customer', function ($query) {
            $query->whereHas('customer');
        });
    }

    private function assignRoleToUsers($roleName, $queryCallback) {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $users = User::where($queryCallback)->doesntHave('roles')->get();
            $users->each(function ($user) use ($role) {
                $user->assignRole($role);
            });
        }
    }
}
