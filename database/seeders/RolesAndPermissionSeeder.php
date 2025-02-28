<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
        Permission::create(['name' => 'edit audit']);
        Permission::create(['name' => 'delete audit']);
        Permission::create(['name' => 'read audit']);


        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // $role = Role::create(['name' => 'user']);
        // $role->givePermissionTo('read audit');


        $role = Role::create(['name' => 'admin'])
            ->givePermissionTo(['read audit', 'edit audit']);

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}
