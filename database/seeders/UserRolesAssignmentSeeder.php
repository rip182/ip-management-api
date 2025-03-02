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
        foreach ($users as $user) {
            if ($user->id == 1) {
                $user->assignRole(Role::SUPER_ADMIN->value);
            } else {
                $user->assignRole(Role::USER->value);
            }
        }
    }
}
