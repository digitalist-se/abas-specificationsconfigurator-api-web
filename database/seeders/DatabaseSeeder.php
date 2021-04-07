<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call(DocumentTextsSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(ChoiceTypeSeeder::class);
        $this->call(ElementSeeder::class);
        $this->call(DemoUserSeeder::class);
        $this->call(BlacklistedEmailDomainSeeder::class);
    }
}
