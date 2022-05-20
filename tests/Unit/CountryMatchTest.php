<?php

namespace Tests\Unit;

use App\Models\Country;
use Tests\TestCase;

class CountryMatchTest extends TestCase
{
    public function scenarios()
    {
        return [
            'de' => [
                'de',
                Country::Germany,
            ],
            'Deutschland' => [
                'Deutschland',
                Country::Germany,
            ],
            'Australia' => [
                'Australia',
                Country::Australia,
            ],
            'Austria' => [
                'Austria',
                Country::Austria,
            ],
            'at' => [
                'at',
                Country::Austria,
            ],
            'Bulgaria' => [
                'Bulgaria',
                Country::Bulgaria,
            ],
            'Canada' => [
                'Canada',
                Country::Canada,
            ],
            'China' => [
                'China',
                Country::China,
            ],
            'Czech Republic' => [
                'Czech Republic',
                Country::Czech_Republic,
            ],
            'France' => [
                'France',
                Country::France,
            ],
            'Germany' => [
                'Germany',
                Country::Germany,
            ],
            'Hungary' => [
                'Hungary',
                Country::Hungary,
            ],
            'India' => [
                'India',
                Country::India,
            ],
            'Italy' => [
                'Italy',
                Country::Italy,
            ],
            'Malaysia' => [
                'Malaysia',
                Country::Malaysia,
            ],
            'Netherlands' => [
                'Netherlands',
                Country::Netherlands,
            ],
            'Poland' => [
                'Poland',
                Country::Poland,
            ],
            'Romania' => [
                'Romania',
                Country::Romania,
            ],
            'Singapore' => [
                'Singapore',
                Country::Singapore,
            ],
            'Slovakia' => [
                'Slovakia',
                Country::Slovakia,
            ],
            'Spain' => [
                'Spain',
                Country::Spain,
            ],
            'Switzerland' => [
                'Switzerland',
                Country::Switzerland,
            ],
            'Turkey' => [
                'Turkey',
                Country::Turkey,
            ],
            'United States' => [
                'United States',
                Country::United_States,
            ],
            'Other' => [
                'Other',
                Country::Other,
            ],
        ];
    }

    /**
     * @dataProvider scenarios
     * @test
     */
    public function it_provide_country_for_old_value($value, Country $expectedCountry)
    {
        $this->assertEquals($expectedCountry, Country::findMatch($value));
    }
}
