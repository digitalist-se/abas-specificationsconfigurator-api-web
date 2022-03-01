<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\PassportTestCase;

class UserControllerTest extends PassportTestCase
{
    use WithFaker;

    protected $role = Role::USER;

    public function test_get_user()
    {
        $response = $this->getJson('/api/user');
        static::assertStatus($response, 200);
        $response->assertJson([
            'name'  => $this->user->name,
            'email' => $this->user->email,
            'role'  => Role::USER,
        ]);
    }

    public function test_update_whole_user()
    {
        $requestBody = [
            'name'                  => 'Max Muster',
            'email'                 => 'max.muster@company.com',

            'sex'                    => 'm',
            'company_name'           => $this->faker->company(),
            'phone'                  => $this->faker->phoneNumber(),
            'website'                => $this->faker->randomAscii(),
            'street'                 => $this->faker->streetAddress(),
            'additional_street_info' => $this->faker->streetAddress(),
            'zipcode'                => $this->faker->randomNumber(5),
            'city'                   => $this->faker->city(),
            'contact'                => $this->faker->name(),
            'contact_function'       => 'Geschäftsführer',
            'country'                => 'Deutschland',
        ];
        $response = $this->putJson('/api/user', $requestBody);
        static::assertStatus($response, 204);
        $response = $this->getJson('/api/user');
        static::assertStatus($response, 200);
        $expectingResponse = $requestBody;
        $expectingResponse['role'] = Role::USER;
        $response->assertJson($expectingResponse);
    }

    public function test_update_email_of_user()
    {
        $requestBody = [
            'email'                 => 'max.muster@company.com',
        ];
        $response = $this->putJson('/api/user', $requestBody);
        static::assertStatus($response, 204);
        $response = $this->getJson('/api/user');
        static::assertStatus($response, 200);
        $expectingResponse = $requestBody;
        $expectingResponse['role'] = Role::USER;
        $response->assertJson($requestBody);
    }
}
