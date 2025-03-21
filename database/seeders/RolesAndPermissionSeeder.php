<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Enums\Permission;
use App\Enums\Role;

class RolesAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        SpatiePermission::create(['name' => Permission::READ_AUDIT->value, 'guard_name' => 'api']);
        SpatiePermission::create(['name' => Permission::EDIT_AUDIT->value, 'guard_name' => 'api']);
        SpatiePermission::create(['name' => Permission::CREATE_IP->value, 'guard_name' => 'api']);
        SpatiePermission::create(['name' => Permission::READ_IP->value, 'guard_name' => 'api']);
        SpatiePermission::create(['name' => Permission::EDIT_IP->value, 'guard_name' => 'api']);
        SpatiePermission::create(['name' => Permission::DELETE_IP->value, 'guard_name' => 'api']);

        $role = SpatieRole::create(['name' => Role::SUPER_ADMIN->value, 'guard_name' => 'api']);
        $role->syncPermissions(SpatiePermission::all());

        $role = SpatieRole::create(['name' => Role::USER->value, 'guard_name' => 'api']);
        $role->syncPermissions([Permission::CREATE_IP->value, Permission::EDIT_IP->value, Permission::READ_IP->value]);
    }
}
