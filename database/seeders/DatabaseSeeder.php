<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $seedingTimes = [];

        $start = hrtime(true);

        $this->call(DocumentTextsSeeder::class);
        $end = hrtime(true);
        $seedingTimes['DocumentTextsSeeder'] = ($end - $start) * 1e-9;
        $start = hrtime(true);
        $this->call(AdminSeeder::class);
        $end = hrtime(true);
        $seedingTimes['AdminSeeder'] = ($end - $start) * 1e-9;
        $start = hrtime(true);
        $this->call(ChoiceTypeSeeder::class);
        $end = hrtime(true);
        $seedingTimes['ChoiceTypeSeeder'] = ($end - $start) * 1e-9;
        $start = hrtime(true);
        $this->call(ElementSeeder::class);
        $end = hrtime(true);
        $seedingTimes['ElementSeeder'] = ($end - $start) * 1e-9;
        $start = hrtime(true);
        $this->call(DemoUserSeeder::class);
        $end = hrtime(true);
        $seedingTimes['DemoUserSeeder'] = ($end - $start) * 1e-9;
        $start = hrtime(true);
        $this->call(BlacklistedEmailDomainSeeder::class);
        $end = hrtime(true);
        $seedingTimes['BlacklistedEmailDomainSeeder'] = ($end - $start) * 1e-9;

        Log::debug('Seeding Times', $seedingTimes);
    }
}
