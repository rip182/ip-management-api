<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Enums\TokenAbility;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Middleware\CheckTokenAbility;

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
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('audits', [
            'user_id' => $this->user->id,
            'event' => 'login',
            'tags' => 'auth,login'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'accessToken',
                'user',
                'role',
            ]);
    }

    /**
     * Test login fails with incorrect password.
     */
    public function test_login_fails_with_incorrect_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid username or password'
            ]);
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

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid username or password']);
    }

    /**
     * Test successful logout with valid token.
     */
    public function test_successful_logout()
    {

        $token = JWTAuth::claims(['abilities' => 'access-api'])->fromUser($this->user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User logged out successfully'
            ]);

        $this->assertFalse(JWTAuth::setToken($token)->check());

        $response->assertCookieExpired('refresh_token');

        $this->assertDatabaseHas('audits', [
            'user_id' => $this->user->id,
            'event' => 'logout',
            'tags' => 'auth,logout'
        ]);
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
     * Test token refresh fails without required ability.
     */
    public function test_token_refresh_fails_without_required_ability()
    {

        $token = JWTAuth::claims(['abilities' => 'access-api'])->fromUser($this->user);

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
        $token = JWTAuth::claims(['abilities' => 'issue-access-token'])->fromUser($this->user);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');

        $response->assertStatus(403);
    }
}
