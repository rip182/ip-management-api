<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InternetProtocol>
 */
class InternetProtocolAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\InternetProtocolAddress::class;

    public function definition(): array
    {
        return [
            'ip_address' => $this->faker->unique()->ipv4(),
            'label'      => $this->faker->word(),
            'comment'    => $this->faker->optional()->sentence(),
            'user_id'    => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
