<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateNameFixData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-names:data {source : the source file} {target : the target file to write}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates File for Fixing Name Migration';

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

        $targetPath = $this->argument('target');
        if (File::exists($targetPath)) {
            $this->error('Target file does already exist');
        }

        $sourceData = $this->readCsv($sourcePath);

        $targetData = collect($sourceData)->map(fn ($row) => $this->parseRow($row));

        $this->writeCsv($targetPath, $targetData->toArray(), ['id', 'first_name', 'last_name']);

        return 0;
    }

    /**
     * @return array|array[]
     */
    protected function readCsv(string $sourcePath): array
    {
        $sourceData = array_map(fn ($line) => str_getcsv($line, ';'), file($sourcePath));
        array_walk($sourceData, static function (&$a) use ($sourceData) {
            $a = array_combine($sourceData[0], $a);
        });
        array_shift($sourceData);

        return $sourceData;
    }

    /**
     * @return array|array[]
     */
    protected function writeCsv(string $targetPath, array $data, ?array $title = null): void
    {
        $fp = fopen($targetPath, 'wb');

        if ($title) {
            fputcsv($fp, $title);
        }

        foreach ($data as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);
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
