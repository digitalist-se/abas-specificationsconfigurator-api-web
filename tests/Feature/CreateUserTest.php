<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\Register;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use WithFaker;

    public function test_create_user()
    {
        Notification::fake();
        $requestBody = [
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
        static::assertStatus($response, 204);
        $user = User::where('email', '=', $requestBody['email'])->first();
        Notification::assertSentTo($user, Register::class, function ($notification) use ($user) {
            return $notification->user->id === $user->id;
        });
        // user was already created.
        // retry creating user, that request should fail
        $response = $this->postJson('/api/user', $requestBody);
        static::assertStatus($response, 422);
    }
}
