<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\InternetProtocolAddress;

use Illuminate\Foundation\Testing\RefreshDatabase;

class InternetProtocolAddressControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_internet_protocol_address(): void
    {
        $user = User::factory()->create();
        $payload = [
            'ip_address' => '192.168.1.1',
            'label'      => 'Server IP',
            'comment'    => 'Main server address',
            'user_id'    => $user->id,
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson(route('internet-protocol-address.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'ip_address' => '192.168.1.1',
                'label'      => 'Server IP',
                'comment'    => 'Main server address',
                'user_id'    => $user->id,
            ]);

        $this->assertDatabaseHas('internet_protocol_addresses', $payload);
    }

    public function test_store_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('internet-protocol-address.store'), []); // Empty payload

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ip_address', 'label']);
    }

    public function test_store_fails_with_invalid_data_types(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('internet-protocol-address.store'), [
                'ip_address' => 12345,             // Integer instead of string
                'label' => ['invalid' => 'array'], // Array instead of string
                'comment' => true,                 // Boolean instead of string
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ip_address', 'label', 'comment'])
            ->assertJsonFragment([
                'errors' => [
                    'ip_address' => ['The ip address field must be a string.'], // Match actual message
                    'label' => ['The label field must be a string.'],
                    'comment' => ['The comment field must be a string.']
                ]
            ]);
    }
}
