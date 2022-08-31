<?php

namespace Tests\Feature;

use App\CRM\Service\CRMService;
use App\Enums\ContactType;
use App\Mail\LeadRegisterMail;
use App\Models\User;
use App\Notifications\Register;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use WithFaker;

    public function test_create_user()
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
        $this->expectSequenceToCrmSystem($requestBody['phone'], ContactType::User);

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
     * @param $userPhone
     *
     * @return void
     */
    private function expectSequenceToCrmSystem($userPhone, ContactType $contactType): void
    {
        $crmService = $this->mock(CRMService::class);

        $expectUser = fn (User $user) => $user->phone === $userPhone;
        $expectUserAndContactType = fn (User $user, ContactType $type) => $expectUser($user) && $type === $contactType;

        $crmService
            ->shouldReceive('upsertCompany')
            ->withArgs($expectUser)
            ->andReturn(true);

        $crmService->shouldReceive('upsertContact')
            ->withArgs($expectUserAndContactType)
            ->andReturn(true);
    }
}
