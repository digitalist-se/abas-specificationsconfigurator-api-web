<?php

namespace Tests\Unit\CRM;

use App\CRM\Adapter\Hubspot\UserNoteAdapter;
use App\Models\User;
use Tests\TestCase;

class NoteAdapterTest extends TestCase
{
    /**
     * @return User
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
        $adapter = $this->app->make(UserNoteAdapter::class);
        $requestBody = $adapter->createNoteBody($user);

        $attributes = collect([
            'salutation',
            'contact_first_name',
            'contact_last_name',
            'contact_email',
            'contact_function',
            'phone',
        ]);

        // We expect that the request body contains expected data
        $attributes->each(
            fn ($attribute) => $this->assertStringContainsString(
                $user->{$attribute},
                $requestBody,
            )
        );
        $this->assertStringContainsString(__('New specification configuration:'), $requestBody);
    }
}
