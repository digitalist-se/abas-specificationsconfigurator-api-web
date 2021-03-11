<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'                   => $this->faker->name,
            'email'                  => $this->faker->unique()->safeEmail,
            'password'               => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'         => Str::random(10),
            'sex'                    => $this->faker->boolean ? 'm' : 'w',
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
    }
}
