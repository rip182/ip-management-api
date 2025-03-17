<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\InternetProtocolAddress;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Foundation\Testing\RefreshDatabase;

class InternetProtocolAddressControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    protected $user;
    protected $token;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\Spatie\Permission\Models\Role::where('name', \App\Enums\Role::SUPER_ADMIN->value)->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => \App\Enums\Role::SUPER_ADMIN->value]);
        }

        if (!\Spatie\Permission\Models\Permission::where('name', \App\Enums\Permission::DELETE_IP->value)->exists()) {
            \Spatie\Permission\Models\Permission::create(['name' => \App\Enums\Permission::DELETE_IP->value]);
        }

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $this->user->givePermissionTo(\App\Enums\Permission::DELETE_IP->value);

        $this->token = JWTAuth::claims(['abilities' => 'access-api'])->fromUser($this->user);
    }

    public function test_store_creates_internet_protocol_address(): void
    {
        config(['audit.console' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/user');
        $data = $response->json();

        $payload = [
            'ip_address' => '192.168.1.1',
            'label'      => 'Server IP',
            'comment'    => 'Main server address',
            'user_id'    => $data['user']['id']
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/internet-protocol-address', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'ip_address' => '192.168.1.1',
                'label'      => 'Server IP',
                'comment'    => 'Main server address',
                'user_id'    => $data['user']['id']
            ]);

        $this->assertDatabaseHas('internet_protocol_addresses', $payload);
    }

    public function test_store_fails_with_invalid_data_format(): void
    {
        config(['audit.console' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/user');

        $data = $response->json();

        $invalidPayload = [
            'ip_address' => ['invalid', 'array'],
            'label'      => 12345,
            'comment'    => true,
            'user_id'    => $data['user']['id']
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/internet-protocol-address', $invalidPayload);

        // Assert 422 Unprocessable Entity
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'ip_address',
                'label',
                'comment'
            ]);
    }


    public function test_show_displays_internet_protocol_address(): void
    {
        config(['audit.console' => true]);

        $internetProtocolAddress = InternetProtocolAddress::factory()->create([
            'user_id'    => $this->user->id,
            'ip_address' => '192.168.1.1',
            'label'      => 'Server IP',
            'comment'    => 'Main server address',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/internet-protocol-address/{$internetProtocolAddress->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id'         => $internetProtocolAddress->id,
                'ip_address' => '192.168.1.1',
                'label'      => 'Server IP',
                'comment'    => 'Main server address',
                'user_id'    => $this->user->id,
            ]);
    }

    public function test_update_modifies_internet_protocol_address(): void
    {
        config(['audit.console' => true]);

        $internetProtocolAddress = InternetProtocolAddress::factory()->create([
            'user_id'    => $this->user->id,
            'ip_address' => '192.168.1.1',
            'label'      => 'Server IP',
            'comment'    => 'Main server address',
        ]);

        $updatedPayload = [
            'ip_address' => '10.0.0.1',
            'label'      => 'Updated Label',
            'comment'    => 'Updated comment',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/internet-protocol-address/{$internetProtocolAddress->id}", $updatedPayload);

        $response->assertStatus(200)
            ->assertJson([
                'id'         => $internetProtocolAddress->id,
                'ip_address' => '10.0.0.1',
                'label'      => 'Updated Label',
                'comment'    => 'Updated comment',
                'user_id'    => $this->user->id,
            ]);

        $this->assertDatabaseHas('internet_protocol_addresses', [
            'id'         => $internetProtocolAddress->id,
            'ip_address' => '10.0.0.1',
            'label'      => 'Updated Label',
            'comment'    => 'Updated comment',
            'user_id'    => $this->user->id,
        ]);

        $this->assertDatabaseHas('audits', [
            'auditable_id'   => $internetProtocolAddress->id,
            'auditable_type' => InternetProtocolAddress::class,
            'event'          => 'updated',
        ]);
    }

    public function test_destroy_deletes_internet_protocol_address(): void
    {
        $this->assertTrue($this->user->hasRole(\App\Enums\Role::SUPER_ADMIN->value));
        $this->assertTrue($this->user->hasPermissionTo(\App\Enums\Permission::DELETE_IP->value));

        $internetProtocolAddress = InternetProtocolAddress::factory()->create([
            'user_id'    => $this->user->id,
            'ip_address' => '192.168.1.1',
            'label'      => 'Server IP',
            'comment'    => 'Main server address',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/internet-protocol-address/{$internetProtocolAddress->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Delete successful']);
    }
}
