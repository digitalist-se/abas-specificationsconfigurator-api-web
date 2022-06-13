<?php

namespace Tests\Unit\CRM;

use App\CRM\Adapter\CompanyAdapter;
use App\Models\User;
use Tests\TestCase;

class CompanyAdapterTest extends TestCase
{
    /**
     * @return \App\Models\User
     */
    protected function user()
    {
        return User::factory()->make();
    }
    /**
     * @test
     */
    public function it_create_request_body()
    {
        // Given is a user
        $user = $this->user();

        // When we pass it to adapter
        $adapter = $this->app->make(CompanyAdapter::class);
        $requestBody = $adapter->toCreateRequestBody($user);

        // We expect that the request body contains expected data
        $this->assertEquals(
            [
                'properties' => [
                    'name'    => $user->company_name,
                    'website' => $user->website,
                    'zip'     => $user->zipcode,
                    'city'    => $user->city,
                    'address' => $user->full_street,
                    'country' => $user->lead_country,
                ],
            ],
            $requestBody
        );
    }
}
