<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name'             => $this->faker->firstName(),
            'last_name'              => $this->faker->lastName(),
            'email'                  => $this->faker->unique()->safeEmail(),
            'password'               => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'         => Str::random(10),
            'sex'                    => $this->faker->boolean() ? 'm' : 'w',
            'company_name'           => $this->faker->company(),
            'phone'                  => $this->faker->phoneNumber(),
            'website'                => $this->faker->randomAscii(),
            'street'                 => $this->faker->streetAddress(),
            'additional_street_info' => $this->faker->streetAddress(),
            'zipcode'                => $this->faker->randomNumber(5),
            'city'                   => $this->faker->city(),
            'contact_first_name'     => $this->faker->firstName(),
            'contact_last_name'      => $this->faker->lastName(),
            'contact_function'       => 'Geschäftsführer',
        ];
    }

    /**
     * Define the model's unverified state.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function guest()
    {
        return $this->state(['role' => Role::GUEST]);
    }

    public function user()
    {
        return $this->state(['role' => Role::USER]);
    }

    public function admin()
    {
        return $this->state(['role' => Role::ADMIN]);
    }
}
