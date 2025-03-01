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

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        SpatiePermission::create(['name' => Permission::READ_AUDIT->value]);
        SpatiePermission::create(['name' => Permission::EDIT_AUDIT->value]);
        SpatiePermission::create(['name' => Permission::DELETE_AUDIT->value]);


        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // $role = Role::create(['name' => 'user']);
        // $role->givePermissionTo('read audit');


        $role = SpatieRole::create(['name' => Role::ADMIN->value])
            ->givePermissionTo([Permission::READ_AUDIT->value, Permission::EDIT_AUDIT->value]);

        $role = SpatieRole::create(['name' => Role::SUPER_ADMIN->value]);
        $role->givePermissionTo(SpatiePermission::all());
    }
}
