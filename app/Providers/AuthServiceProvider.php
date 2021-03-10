<?php

namespace App\Providers;

use App\Policies\AnswerPolicy;
use App\Policies\TextPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model'         => 'App\Policies\ModelPolicy',
        'App\Models\Text'   => TextPolicy::class,
        'App\Models\Answer' => AnswerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
        Passport::tokensExpireIn(now()->addDays(1));
        Passport::refreshTokensExpireIn(now()->addDays(5));
    }
}
