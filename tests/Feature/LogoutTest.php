<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\PassportTestCase;

class LogoutTest extends PassportTestCase
{
    protected $role        = Role::USER;

    protected function isTokenValid(User $user): bool
    {
        /** @var \Laravel\Passport\Token $token */
        $token = $user->tokens->firstWhere('id', $this->token->id);

        return $token && !$token->revoked;
    }

    public function testLogoutOfLoggedInUser()
    {
        // Given we have a logged in user
        $this->assertTrue($this->isTokenValid($this->user));

        // When we call the logout endpoint
        $response = $this->get('/api/logout');

        // Then we expect a successful response
        $response->assertStatus(204);

        // And we expect the user is not authenticated anymore
        $this->assertFalse($this->isTokenValid($this->user->refresh()));
    }

    public function testLogoutOfNotLoggedInUser()
    {
        // When we call the logout endpoint without authorization
        $response = $this->getRequestWithoutAuthorization('/api/logout');

        // Then we expect a non authenticated response
        $response->assertStatus(401);
    }
}
