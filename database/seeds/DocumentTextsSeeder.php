<?php

use Illuminate\Database\Seeder;

class DocumentTextsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
    }

    protected function text($key, $value)
    {
        \App\Models\Text::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
