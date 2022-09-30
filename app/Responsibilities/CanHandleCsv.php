<?php

namespace App\Responsibilities;

trait CanHandleCsv
{
    /**
     * @return array|array[]
     */
    protected function readCsv(string $sourcePath, string $separator = ',', bool $hasHeaderRow = true): array
    {
        $sourceData = array_map(fn ($line) => str_getcsv($line, $separator), file($sourcePath));
        if (!$hasHeaderRow) {
            return $sourceData;
        }
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
}
