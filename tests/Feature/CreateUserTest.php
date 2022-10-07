<?php

namespace Tests\Feature;

use App\CRM\Service\CRMService;
use App\Mail\LeadRegisterMail;
use App\Models\BlacklistedEmailDomain;
use App\Models\User;
use App\Notifications\Register;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\AssertsCRMHandlesEvents;

class CreateUserTest extends TestCase
{
    use WithFaker;
    use AssertsCRMHandlesEvents;

    /**
     * @test
     */
    public function create_user(): void
    {
        Mail::fake();
        Notification::fake();
        $requestBody = [
            'name'                  => 'Max Muster',
            'email'                 => 'max.muster@company.com',
            'password'              => 'test1234',
            'password_confirmation' => 'test1234',

            'sex'                    => 'm',
            'company_name'           => $this->faker->company(),
            'phone'                  => $this->faker->phoneNumber(),
            'website'                => $this->faker->randomAscii(),
            'street'                 => $this->faker->streetAddress(),
            'additional_street_info' => $this->faker->streetAddress(),
            'zipcode'                => $this->faker->randomNumber(5),
            'city'                   => $this->faker->city(),
            'contact'                => $this->faker->name(),
            'contact_function'       => 'Geschäftsführer',
        ];
        $this->assertCRMServiceHandlesUserRegistered($this->mock(CRMService::class), $requestBody);

        $response = $this->postJson('/api/user', $requestBody);
        static::assertStatus($response, 204);
        $user = User::where('email', '=', $requestBody['email'])->first();
        Notification::assertSentTo($user, Register::class, function ($notification) use ($user) {
            return $notification->user->id === $user->id;
        });
        Mail::assertQueued(LeadRegisterMail::class, function (LeadRegisterMail $mail) use ($user) {
            return $mail->leadUser->id == $user->id;
        });
        // user was already created.
        // retry creating user, that request should fail
        $response = $this->postJson('/api/user', $requestBody);
        static::assertStatus($response, 422);
    }

    /**
     * @test
     * @dataProvider provideBlacklistedEmailDomains
     */
    public function not_create_user_with_blacklisted_email_domain(string $blacklistedDomain): void
    {
        $requestBody = [
            'name'                  => 'Max Muster',
            'email'                 => 'max.muster@'.$blacklistedDomain,
            'password'              => 'test1234',
            'password_confirmation' => 'test1234',

            'sex'                    => 'm',
            'company_name'           => $this->faker->company(),
            'phone'                  => $this->faker->phoneNumber(),
            'website'                => $this->faker->randomAscii(),
            'street'                 => $this->faker->streetAddress(),
            'additional_street_info' => $this->faker->streetAddress(),
            'zipcode'                => $this->faker->randomNumber(5),
            'city'                   => $this->faker->city(),
            'contact'                => $this->faker->name(),
            'contact_function'       => 'Geschäftsführer',
        ];

        $response = $this->postJson('/api/user', $requestBody);
        static::assertStatus($response, 422);
        $msg = __('validation.custom.email.checkdomains');
        $response->assertJson([
            'message' => $msg,
            'errors'  => ['email' => [$msg]],
        ]);
    }

    public function provideBlacklistedEmailDomains(): array
    {
        return collect([
            'web.de',
            'googlemail.com',
            'gmx.net',
        ])
            ->mapWithKeys(fn (string $domain) => [$domain => [$domain]])
            ->toArray();
    }
}
