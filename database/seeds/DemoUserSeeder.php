<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

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
                'name'     => 'Demo User',
                'password' => Hash::make('demodemo'),
                'role'     => Role::USER,
            ]
        );
    }
}
