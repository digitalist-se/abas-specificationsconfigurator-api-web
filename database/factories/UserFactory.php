<?php

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/
/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Faker $faker) {
    return [
        'name'           => $faker->name,
        'email'          => $faker->unique()->safeEmail,
        'password'       => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),

        'sex'                    => $faker->boolean ? 'm' : 'w',
        'company_name'           => $faker->company,
        'phone'                  => $faker->phoneNumber,
        'website'                => $faker->randomAscii,
        'street'                 => $faker->streetAddress,
        'additional_street_info' => $faker->streetAddress,
        'zipcode'                => $faker->randomNumber(5),
        'city'                   => $faker->city,
        'contact'                => $faker->name,
        'contact_function'       => 'Geschäftsführer',
    ];
});
