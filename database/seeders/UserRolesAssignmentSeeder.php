<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Adjust based on your User model location
use App\Enums\Role;

class UserRolesAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assign "super admin" role to user with ID 1
        $firstUser = User::find(1);
        if ($firstUser) {
            $firstUser->assignRole(Role::SUPER_ADMIN->value);
        }
    }
}
