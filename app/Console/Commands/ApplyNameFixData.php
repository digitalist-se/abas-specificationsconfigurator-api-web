<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Responsibilities\CanHandleCsv;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ApplyNameFixData extends Command
{
    use CanHandleCsv;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-names:apply {source : the source file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Applies the name data given at source files to the user object';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sourcePath = $this->argument('source');
        if (! File::exists($sourcePath)) {
            $this->error('Source file does not exist');
        }

        /** @var \Illuminate\Support\Collection|array{id:int, first_name:null|string, last_name:null|string}[] $data */
        $data = collect($this->readCsv($sourcePath, ','))
            ->keyBy('id');

        $this->info("{$data->count()} data rows given");

        $noInsertDefaults = ['email' => 'no-insert@mail.com', 'role' => 0, 'password' => 'no-insert'];
        $upsertData = User::whereKey($data->keys())
            ->whereNull(['first_name', 'last_name'])
            ->pluck('id')
            ->map(fn ($id)  => $data->get($id))
            ->map(fn ($row) => array_merge($row, $noInsertDefaults));

        $this->info("{$upsertData->count()} user rows should be updated");

        if (! $this->confirm('Are you sure to apply data?')) {
            $this->info('No user rows are updated');

            return 0;
        }

        $affected = User::upsert($upsertData->toArray(), 'id', ['first_name', 'last_name']);
        $updated = $affected / 2;

        $this->info("{$updated} user rows are updated");

        return 0;
    }

    /**
     * @param array{id:int, name:null|string, email:string} $row
     *
     * @return array{id:int, first_name:null|string, last_name:null|string}
     */
    protected function parseRow(array $row): array
    {
        [$firstName, $lastName] = $this->parseNames($row['name']);

        if (empty($firstName) && empty($lastName)) {
            [$firstName, $lastName] = $this->parseEmail($row['email']);
        }

        return [
            'id'         => $row['id'],
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ];
    }

    /**
     * @return array<string|null, string|null>
     */
    protected function parseNames(?string $name): array
    {
        if (empty($name)) {
            return [
                null,
                null,
            ];
        }
        $nameParts = explode(' ', $name);

        if (count($nameParts) < 2) {
            return [
                $name,
                null,
            ];
        }
        $lastName = array_pop($nameParts);

        return [
            implode(' ', $nameParts),
            $lastName,
        ];
    }

    /**
     * @return array<string|null, string|null>
     */
    protected function parseEmail(string $email): array
    {
        if (empty($email)) {
            return [
                null,
                null,
            ];
        }

        $nameParts = Str::of(strstr($email, '@', true))
            ->replaceMatches('/[\._-]/', ' ')
            ->replaceMatches('/[0-9]/', '')
            ->headline()
            ->explode(' ');

        if ($nameParts->count() < 2) {
            return [
                $nameParts->first(),
                null,
            ];
        }
        $lastName = $nameParts->pop();

        return [
            $nameParts->implode(' '),
            $lastName,
        ];
    }
}
