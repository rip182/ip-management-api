<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\InternetProtocol;
use App\Models\InternetProtocolAddress;
use App\Models\User;

class InternetProtocolAddressTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_internet_protocol_model(): void
    {
        $ipAddress = new InternetProtocolAddress();
        $this->assertEquals([], $ipAddress->getGuarded());
    }

    public function test_belongs_to_a_user(): void
    {
        $ipAddress = InternetProtocolAddress::factory()->create();

        $this->assertInstanceOf(User::class, $ipAddress->user);
    }
}
