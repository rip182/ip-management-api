<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Enums\TokenAbility;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    protected $user;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Test successful login with correct credentials.
     */
    public function test_successful_login()
    {

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['accessToken', 'refreshToken']);

        $accessToken = $response->json('accessToken');
        $refreshToken = $response->json('refreshToken');

        $this->assertCount(2, $this->user->tokens);

        $accessTokenRecord = PersonalAccessToken::findToken($accessToken);
        $this->assertNotNull($accessTokenRecord);
        $this->assertEquals('access_token', $accessTokenRecord->name);
        $this->assertEquals([TokenAbility::ACCESS_API->value], $accessTokenRecord->abilities);
        $this->assertTrue(
            $accessTokenRecord->expires_at->diffInSeconds(
                Carbon::now()->addMinutes(config('sanctum.access_token_expiration'))
            ) < 5,
            'Access token expiration time is not within expected range'
        );

        $refreshTokenRecord = PersonalAccessToken::findToken($refreshToken);
        $this->assertNotNull($refreshTokenRecord);
        $this->assertEquals('refresh_token', $refreshTokenRecord->name);
        $this->assertEquals([TokenAbility::ISSUE_ACCESS_TOKEN->value], $refreshTokenRecord->abilities);
        $this->assertTrue(
            $refreshTokenRecord->expires_at->diffInSeconds(
                Carbon::now()->addMinutes(config('sanctum.refresh_access_expiration'))
            ) < 5,
            'Refresh token expiration time is not within expected range'
        );
    }

    /**
     * Test login fails with incorrect password.
     */
    public function test_login_fails_with_incorrect_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => ['Username or password incorrect']]);

        $this->assertCount(0, $this->user->tokens);
    }

    /**
     * Test login fails with non-existent email.
     */
    public function test_login_fails_with_nonexistent_email()
    {

        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => ['Username or password incorrect']]);
    }

    /**
     * Test successful logout with valid token.
     */
    public function test_successful_logout()
    {

        $token = $this->user->createToken('access_token', [TokenAbility::ACCESS_API->value])->plainTextToken;


        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');


        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'User logged out successfully']);

        $this->assertNull(PersonalAccessToken::findToken($token));
    }

    /**
     * Test logout fails without authentication.
     */
    public function test_logout_fails_without_authentication()
    {

        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /**
     * Test successful token refresh with valid refresh token.
     */
    public function test_successful_token_refresh()
    {

        $refreshToken = $this->user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $refreshToken)
            ->getJson('/api/auth/refresh-token');


        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'token', 'refreshToken']);

        $this->assertCount(2, $this->user->tokens);
    }

    /**
     * Test token refresh fails without required ability.
     */
    public function test_token_refresh_fails_without_required_ability()
    {

        $token = $this->user->createToken('access_token', [TokenAbility::ACCESS_API->value])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/refresh-token');

        $response->assertStatus(403);
    }

    /**
     * Test token refresh fails without authentication.
     */
    public function test_token_refresh_fails_without_authentication()
    {
        $response = $this->getJson('/api/auth/refresh-token');

        $response->assertStatus(401);
    }

    public function test_token_refresh_fails_when_used_as_access_api()
    {
        $token = $this->user->createToken('access_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value])->plainTextToken;
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');

        $response->assertStatus(403);
    }
}
