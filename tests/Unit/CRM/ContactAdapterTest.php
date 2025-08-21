<?php

namespace Tests\Unit\CRM;

use App\CRM\Adapter\Hubspot\CompanyContactAdapter;
use App\CRM\Adapter\Hubspot\UserContactAdapter;
use App\Models\User;
use Tests\TestCase;

class ContactAdapterTest extends TestCase
{
    /**
     * @return \App\Models\User
     */
    protected function user()
    {
        return User::factory()->make();
    }

    public function provideCustomUserProperties()
    {
        return [
            'no_custom_properties' => [[]],
            'custom_properties'    => [['custum_prop' => 'custom_value']],
        ];
    }

    /**
     * @dataProvider provideCustomUserProperties
     * @test
     */
    public function it_create_request_body_for_user_contact(array $customProperties)
    {
        // Given is a user
        $user = $this->user();

        // When we pass it to adapter
        $adapter = $this->app->make(UserContactAdapter::class);
        $requestBody = $adapter->toRequestBody($user, $customProperties);

        // We expect that the request body contains expected data
        $expectedProperties = array_merge([
            'firstname' => $user->first_name,
            'lastname'  => $user->last_name,
            'email'     => $user->email,
            'company'   => $user->company,
        ], $customProperties);
        $this->assertEquals(
            ['properties' => $expectedProperties],
            $requestBody
        );
    }

    /**
     * @test
     */
    public function it_create_request_body_for_company_contact()
    {
        // Given is a user
        $user = $this->user();

        // When we pass it to adapter
        $adapter = $this->app->make(CompanyContactAdapter::class);
        $requestBody = $adapter->toRequestBody($user);

        // We expect that the request body contains expected data
        $this->assertEquals(
            [
                'properties' => [
                    'salutation' => $user->salutation,
                    'firstname'  => $user->contact_first_name,
                    'lastname'   => $user->contact_last_name,
                    'email'      => $user->contact_email,
                    'jobtitle'   => $user->contact_function,
                    'phone'      => $user->phone,
                    'company'    => $user->company,
                ],
            ],
            $requestBody
        );
    }
}
