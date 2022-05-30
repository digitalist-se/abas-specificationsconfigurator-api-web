<?php

namespace Tests\Unit\CRM;

use App\CRM\Adapter\CompanyAdapter;
use App\CRM\Adapter\ContactAdapter;
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
    public function it_create_request_body()
    {
        // Given is a user
        $user = $this->user();

        // When we pass it to adapter
        $adapter = $this->app->make(ContactAdapter::class);
        $requestBody = $adapter->toCreateRequestBody($user);

        // We expect that the request body contains expected data
        $this->assertEquals(
            [
                'properties' => [
                    'salutation' => $user->salutation,
                    'firstname'  => $user->contact_first_name,
                    'lastname'   => $user->contact_last_name,
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                    'jobtitle'   => $user->contact_function,
                ],
            ],
            $requestBody
        );
    }
}
