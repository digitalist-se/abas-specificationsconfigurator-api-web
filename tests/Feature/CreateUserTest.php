<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use WithFaker;

    public function testCreateUser()
    {
        $requestBody      = [
            'name'                  => 'Max Muster',
            'email'                 => 'max.muster@company.com',
            'password'              => 'test1234',
            'password_confirmation' => 'test1234',

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
        ];
        $response = $this->postJson('/api/user', $requestBody);
        $this->assertStatus($response, 204);
        // user was already created.
        // retry creating user, that request should fail
        $response = $this->postJson('/api/user', $requestBody);
        $this->assertStatus($response, 422);
    }
}
