<?php

namespace Tests\Feature;

use App\Models\Country;
use Tests\TestCase;

class CountryListingTest extends TestCase
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
    public function it_provide_german_country_options($locale)
    {
        $response = $this->getJson('/api/countries', ['ACCEPT_LANGUAGE' => $locale]);
        $this->assertStatus($response, 200);

        $this->assertCount(count(Country::cases()), $response->json());
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
            ],
        ]);
    }
}
