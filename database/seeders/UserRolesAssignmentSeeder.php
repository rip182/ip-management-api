<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Role;

class UserRolesAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $firstUser = true;

        foreach ($users as $user) {
            if ($firstUser) {
                $user->assignRole(Role::SUPER_ADMIN->value);
                $firstUser = false;
            } else {
                $user->assignRole(Role::USER->value);
            }
        }
    }
}
