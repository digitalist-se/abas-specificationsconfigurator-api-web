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
                'name'     => 'Dimitri Pfaffenrodt',
                'password' => Hash::make('svWn5XAb36J25NXj'),
                'role'     => Role::ADMIN,
            ]
        );
        User::firstOrCreate(
            ['email' => 'hagen.pommer@gal-digital.de'],
            [
                'name'     => 'Hagen Pommer',
                'password' => Hash::make('svWn5XAb36J25NXj'),
                'role'     => Role::ADMIN,
            ]
        );
        User::firstOrCreate(
            ['email' => 'kristian.kraft@gal-digital.de'],
            [
                'name'     => 'Kristian Kraft',
                'password' => Hash::make('svWn5XAb36J25NXj'),
                'role'     => Role::ADMIN,
            ]
        );
    }
}
