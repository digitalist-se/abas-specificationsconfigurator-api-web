<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
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
        $company = $this->faker->company();

        return [
            'first_name'             => $this->faker->firstName(),
            'last_name'              => $this->faker->lastName(),
            'email'                  => $this->faker->unique()->safeEmail(),
            'password'               => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role'                   => Role::USER,
            'remember_token'         => Str::random(10),
            'sex'                    => $this->faker->boolean() ? 'm' : 'w',
            'user_company'           => $company,
            'phone'                  => $this->faker->phoneNumber(),
            'website'                => $this->faker->url(),
            'street'                 => $this->faker->streetAddress(),
            'additional_street_info' => $this->faker->streetAddress(),
            'zipcode'                => $this->faker->randomNumber(5),
            'city'                   => $this->faker->city(),
            'company_name'           => $company,
            'contact_first_name'     => $this->faker->firstName(),
            'contact_last_name'      => $this->faker->lastName(),
            'contact_email'          => $this->faker->email(),
            'contact_function'       => 'Geschäftsführer',
            'country'                => $this->faker->country(),
            'email_verified_at'      => Carbon::now()->subDay(),
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

    public function registered()
    {
        return $this->state([
            'email_verified_at'      => null,
            'sex'                    => null,
            'phone'                  => null,
            'website'                => null,
            'street'                 => null,
            'additional_street_info' => null,
            'zipcode'                => null,
            'city'                   => null,
            'country'                => null,
            'company_name'           => null,
            'contact_first_name'     => null,
            'contact_last_name'      => null,
            'contact_email'          => null,
            'contact_function'       => null,
        ]);
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
