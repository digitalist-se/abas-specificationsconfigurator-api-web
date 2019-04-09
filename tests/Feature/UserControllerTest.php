<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\PassportTestCase;

class UserControllerTest extends PassportTestCase
{
    protected $role = Role::USER;
    use WithFaker;

    public function testGetUser()
    {
        $response = $this->getJson('/api/user');
        $this->assertStatus($response, 200);
        $response->assertJson([
            'name'  => $this->user->name,
            'email' => $this->user->email,
            'role'  => Role::USER,
        ]);
    }

    public function testUpdateWholeUser()
    {
        $requestBody = [
            'name'                  => 'Max Muster',
            'email'                 => 'max.muster@company.com',

            'sex'                    => 'm',
            'company_name'           => $this->faker->company,
            'phone'                  => $this->faker->phoneNumber,
            'website'                => $this->faker->randomAscii,
            'street'                 => $this->faker->streetAddress,
            'additional_street_info' => $this->faker->streetAddress,
            'zipcode'                => $this->faker->randomNumber(5),
            'city'                   => $this->faker->city,
            'contact'                => $this->faker->name,
            'contact_function'       => 'Geschäftsführer',
            'country'                => 'Deutschland',
        ];
        $response = $this->putJson('/api/user', $requestBody);
        $this->assertStatus($response, 204);
        $response = $this->getJson('/api/user');
        $this->assertStatus($response, 200);
        $expectingResponse         = $requestBody;
        $expectingResponse['role'] = Role::USER;
        $response->assertJson($expectingResponse);
    }

    public function testUpdateEmailOfUser()
    {
        $requestBody = [
            'email'                 => 'max.muster@company.com',
        ];
        $response = $this->putJson('/api/user', $requestBody);
        $this->assertStatus($response, 204);
        $response = $this->getJson('/api/user');
        $this->assertStatus($response, 200);
        $expectingResponse         = $requestBody;
        $expectingResponse['role'] = Role::USER;
        $response->assertJson($requestBody);
    }
}
