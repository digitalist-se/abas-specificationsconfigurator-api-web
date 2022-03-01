<?php

namespace Tests\Unit;

use App\Models\Locale;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    public function localizedImageAssetsProvider()
    {
        $image = 'logo_white.png';

        return [
            'de' => [
                Locale::DE,
                $image,
            ],
            'en' => [
                Locale::EN,
                $image,
            ],
            'fallback' => [
                null,
                $image,
            ],
        ];
    }

    /**
     * @dataProvider localizedImageAssetsProvider
     */
    public function test_images_assets_could_be_localized(?string $givenLocale, string $imagePath)
    {
        // Given we have an explicit locale (or the fallback locale) and an expected URL
        $expectedUrl = asset('images/'.Locale::current()->getValue().'/'.$imagePath);
        if ($givenLocale) {
            App::setLocale($givenLocale);
            $expectedUrl = asset('images/'.$givenLocale.'/'.$imagePath);
        }

        // When request a localized image url
        $url = Locale::imageAsset($imagePath);

        // Then we expect to get the correct url
        self::assertEquals($expectedUrl, $url);
    }
}
