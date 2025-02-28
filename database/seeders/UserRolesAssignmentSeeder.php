<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Adjust based on your User model location

class UserRoleAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assign "super admin" role to user with ID 1
        $firstUser = User::find(1);
        if ($firstUser) {
            $firstUser->assignRole('super admin');
        } else {
            throw new \Exception('User with ID 1 not found. Ensure your user seeder has run.');
        }
    }
}
