<?php

namespace Database\Seeders;

use App\Models\Text;
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
        Text::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
