<?php

use Faker\Generator as Faker;

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

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'name'           => $faker->name,
        'email'          => $faker->unique()->safeEmail,
        'password'       => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),

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
