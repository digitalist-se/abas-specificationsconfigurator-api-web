<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Tests\PassportTestCase;

class UpdatePasswordTest extends PassportTestCase
{
    protected $role        = Role::USER;
    const CURRENT_PASSWORD = 'oldPassword1234';

    public function setUp()
    {
        parent::setUp();
        $this->user->update(['password' => Hash::make(self::CURRENT_PASSWORD)]);
    }

    public function testUpdatePasswordWithoutConfirmAndOld()
    {
        $requestBody          = [
            'password' => 'test1234',
        ];
        $response = $this->putJson('/api/password', $requestBody);
        $this->assertStatus($response, 422);
        $this->user->refresh();
        $this->assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->password));
    }

    public function testUpdatePasswordWithoutOld()
    {
        $requestBody          = [
            'password'              => 'test1234',
            'password_confirmation' => 'test1234',
        ];
        $response = $this->putJson('/api/password', $requestBody);
        $this->assertStatus($response, 422);
        $this->user->refresh();
        $this->assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->password));
    }

    public function testUpdatePasswordWithoutConfirm()
    {
        $requestBody          = [
            'password'     => 'test1234',
            'password_old' => self::CURRENT_PASSWORD,
        ];
        $response = $this->putJson('/api/password', $requestBody);
        $this->assertStatus($response, 422);
        $this->user->refresh();
        $this->assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->password));
    }

    public function testUpdatePassword()
    {
        $requestBody          = [
            'password'              => 'test1234',
            'password_confirmation' => 'test1234',
            'password_old'          => self::CURRENT_PASSWORD,
        ];
        $response = $this->putJson('/api/password', $requestBody);
        $this->assertStatus($response, 204);
        $this->user->refresh();
        $this->assertTrue(Hash::check('test1234', $this->user->password));
    }
}
