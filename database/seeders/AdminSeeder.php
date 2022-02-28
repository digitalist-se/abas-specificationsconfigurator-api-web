<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'dimitri.pfaffenrodt@gal-digital.de'],
            [
                'first_name' => 'Dimitri',
                'last_name'  => 'Pfaffenrodt',
                'password'   => Hash::make('svWn5XAb36J25NXj'),
                'role'       => Role::ADMIN,
            ]
        );
        User::firstOrCreate(
            ['email' => 'hagen.pommer@gal-digital.de'],
            [
                'first_name'     => 'Hagen',
                'last_name'     => 'Pommer',
                'password' => Hash::make('svWn5XAb36J25NXj'),
                'role'     => Role::ADMIN,
            ]
        );
        User::firstOrCreate(
            ['email' => 'kristian.kraft@gal-digital.de'],
            [
                'first_name'     => 'Kristian',
                'last_name'     => 'Kraft',
                'password' => Hash::make('svWn5XAb36J25NXj'),
                'role'     => Role::ADMIN,
            ]
        );
        User::firstOrCreate(
            ['email' => 'marketing@abas.de'],
            [
                'first_name'     => 'abas',
                'last_name'     => 'Marketing',
                'password' => Hash::make('svWn5XAb36J25NXj'),
                'role'     => Role::ADMIN,
            ]
        );
    }
}
