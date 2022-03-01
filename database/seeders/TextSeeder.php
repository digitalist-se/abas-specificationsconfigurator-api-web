<?php

namespace Database\Seeders;

use App\Models\Locale;
use App\Models\Text;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class TextSeeder extends Seeder
{
    private string $baseLocale = Locale::DE;

    private function localesToSync(): array
    {
        $localesToImport = Locale::supportedSet();
        $localesToImport->detach($this->baseLocale);

        return $localesToImport->getValues();
    }

    /**
     * Sync text keys for all supported locales
     */
    public function run()
    {
        $baseTexts = Text::whereLocale($this->baseLocale)
            ->get()
            ->keyBy('key');

        foreach ($this->localesToSync() as $locale) {
            $texts = Text::whereLocale($locale)
                ->get()
                ->keyBy('key');

            $createValues = $baseTexts->diffKeys($texts)
                ->map(function (Text $baseText) use ($locale) {
                    $baseTextValues = $baseText->only([
                        'key',
                        'locale',
                        'value',
                        'description',
                        'public',
                    ]);
                    $localeValues = [
                        'id' => Uuid::uuid4()->toString(),
                        'locale' => $locale,
                    ];

                    return array_merge($baseTextValues, $localeValues);
                })
                ->values()
                ->toArray();

            Text::upsert($createValues, ['key', 'locale'], []);
        }
    }
}
