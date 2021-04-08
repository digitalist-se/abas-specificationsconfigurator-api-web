<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\PassportTestCase;

class UpdatePasswordTest extends PassportTestCase
{
    protected $role        = Role::USER;
    const CURRENT_PASSWORD = 'oldPassword1234';

    public function setUp(): void
    {
        parent::setUp();
        $this->user->update(['password' => Hash::make(self::CURRENT_PASSWORD)]);
    }

    public function test_send_reset_password()
    {
        Notification::fake();
        $response = $this->postJson('/api/password/email', ['email' => $this->user->email]);
        $user     = $this->user;
        static::assertStatus($response, 204);
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            return $notification->user->id === $user->id;
        });
    }

    public function test_update_password_without_confirm_and_old()
    {
        $requestBody          = [
            'password' => 'test1234',
        ];
        $response = $this->putJson('/api/password', $requestBody);
        static::assertStatus($response, 422);
        $this->user->refresh();
        static::assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->password));
    }

    public function test_update_password_without_old()
    {
        $requestBody          = [
            'password'              => 'test1234',
            'password_confirmation' => 'test1234',
        ];
        $response = $this->putJson('/api/password', $requestBody);
        static::assertStatus($response, 422);
        $this->user->refresh();
        static::assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->password));
    }

    public function test_update_password_without_confirm()
    {
        $requestBody          = [
            'password'     => 'test1234',
            'password_old' => self::CURRENT_PASSWORD,
        ];
        $response = $this->putJson('/api/password', $requestBody);
        static::assertStatus($response, 422);
        $this->user->refresh();
        static::assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->password));
    }

    public function test_update_password()
    {
        $requestBody          = [
            'password'              => 'test1234',
            'password_confirmation' => 'test1234',
            'password_old'          => self::CURRENT_PASSWORD,
        ];
        $response = $this->putJson('/api/password', $requestBody);
        static::assertStatus($response, 204);
        $this->user->refresh();
        static::assertTrue(Hash::check('test1234', $this->user->password));
    }
}
