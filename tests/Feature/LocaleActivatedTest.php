<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Passport;
use Tests\TestCase;

class LocaleActivatedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.activated_locales', ['de']);
    }

    /**
     * @test
     */
    public function it_provide_list_of_activated_locales_for_guests()
    {
        $response = $this->getJson('/api/locales/activated');
        $this->assertStatus($response, 200);
        $response->assertJson([
            'de'
        ]);
    }

    /**
     * @test
     */
    public function it_provide_list_of_activated_locales_for_user()
    {
        $user = User::factory()->user()->create();
        $this->assertEquals(Role::USER(), $user->role);
        Passport::actingAs($user);
        $response = $this->getJson('/api/locales/activated');
        $this->assertStatus($response, 200);
        $response->assertJson([
            'de'
        ]);
    }

    /**
     * @test
     */
    public function it_provide_list_of_activated_locales_for_admin()
    {
        $user = User::factory()->admin()->create();
        $this->assertEquals(Role::ADMIN(), $user->role);
        Passport::actingAs($user);
        $response = $this->getJson('/api/locales/activated');
        $this->assertStatus($response, 200);
        $response->assertJson([
            'de',
            'en',
        ]);
    }
}
