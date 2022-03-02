<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'demo@gal-digital.de'],
            [
                'first_name' => 'Demo',
                'last_name'  => 'User',
                'password'   => Hash::make('demodemo'),
                'role'       => Role::USER,
            ]
        );
    }
}
