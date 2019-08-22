<?php

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
            while (($line = fgets($fd)) !== false) {
                $domainName = strtolower(trim($line));
                BlacklistedEmailDomain::firstOrCreate(['name' => $domainName]);
            }

            fclose($fd);
        } else {
            // error opening the file.

        }

    }
}


