<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Support\Facades\Config;
use Tests\PassportTestCase;

class LocaleActivatedByAdminTest extends PassportTestCase
{
    protected $role = Role::ADMIN;

    public function setUp(): void
    {
        parent::setUp();

        Config::set('app.activated_locales', ['de']);
    }

    /**
     * @test
     */
    public function it_provide_list_of_activated_locales_for_admin()
    {
        $this->assertEquals(Role::ADMIN(), $this->user->role);
        $response = $this->getJson('/api/locales/activated');
        $this->assertStatus($response, 200);
        $response->assertJson([
            'de',
            'en',
        ]);
    }
}
