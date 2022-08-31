<?php

namespace Tests\Unit\CRM;

use App\CRM\Adapter\CompanyContactAdapter;
use App\CRM\Adapter\UserContactAdapter;
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

    /**
     * @test
     */
    public function it_create_request_body_for_user_contact()
    {
        // Given is a user
        $user = $this->user();

        // When we pass it to adapter
        $adapter = $this->app->make(UserContactAdapter::class);
        $requestBody = $adapter->toCreateRequestBody($user);

        // We expect that the request body contains expected data
        $this->assertEquals(
            [
                'properties' => [
                    'firstname' => $user->first_name,
                    'lastname'  => $user->last_name,
                    'email'     => $user->email,
                    'company'   => $user->company,
                ],
            ],
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
        $requestBody = $adapter->toCreateRequestBody($user);

        // We expect that the request body contains expected data
        $this->assertEquals(
            [
                'properties' => [
                    'salutation' => $user->salutation,
                    'firstname'  => $user->contact_first_name,
                    'lastname'   => $user->contact_last_name,
                    // TODO: user needs a contact_email field
                    //                    'email'      => $user->contact_email,
                    'jobtitle' => $user->contact_function,
                    'phone'    => $user->phone,
                    'company'  => $user->company,
                ],
            ],
            $requestBody
        );
    }
}
