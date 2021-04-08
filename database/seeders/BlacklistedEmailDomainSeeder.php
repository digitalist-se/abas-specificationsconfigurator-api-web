<?php

namespace Database\Seeders;

use App\Models\BlacklistedEmailDomain;
use Illuminate\Database\Seeder;

class BlacklistedEmailDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BlacklistedEmailDomain::truncate();

        $fileName = resource_path('validation') . '/blacklisted_domains.txt';

        $fd = fopen($fileName, 'r');
        if ($fd) {
            $data = [];
            while (($line = fgets($fd)) !== false) {
                $domainName = strtolower(trim($line));
                $data[] = ['name' => $domainName];
            }

            BlacklistedEmailDomain::upsert($data, ['name'], ['name']);

            fclose($fd);
        } else {
            // error opening the file.

        }

    }
}


