<?php

namespace Tests\Unit;

use App\Models\Country;
use Tests\TestCase;

class CountryTest extends TestCase
{
    public function locales()
    {
        return [
            'de' => ['de'],
            'en' => ['en'],
        ];
    }

    /**
     * @dataProvider locales
     * @test
     */
    public function it_provide_display_names($locale)
    {
        app()->setLocale($locale);
        collect(Country::cases())->each(
            fn (Country $country) => $this->assertNotNull($country->getDisplayName())
        );
    }
}
