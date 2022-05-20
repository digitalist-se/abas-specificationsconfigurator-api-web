<?php

namespace App\Providers;

use App\Models\Chapter;
use App\Models\Element;
use App\Models\Section;
use App\Models\Text;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->configureRateLimiting();

        $this->bindTextById();
        $this->bindChapterById();
        $this->bindSectionById();
        $this->bindElementById();

        $this->routes(function () {
            $this->mapApiRoutes();
            $this->mapAuthorizedApiRoutes();

            $this->mapWebRoutes();
            $this->mapAppRoutes();
            $this->mapTestMailRoutes();
        });
    }

    protected function bindTextById()
    {
        Route::bind('text', function ($value) {
            return Text::findOrFail($value) ?? abort(404);
        });
    }

    protected function bindChapterById()
    {
        Route::bind('chapter', function ($value) {
            return Chapter::findOrFail($value) ?? abort(404);
        });
    }

    protected function bindSectionById()
    {
        Route::bind('section', function ($value) {
            return Section::findOrFail($value) ?? abort(404);
        });
    }

    protected function bindElementById()
    {
        Route::bind('element', function ($value) {
            return Element::findOrFail($value) ?? abort(404);
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapAppRoutes()
    {
        Route::domain(config('app.app-url'))
            ->group(base_path('routes/app.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "auth:api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapAuthorizedApiRoutes()
    {
        Route::prefix('api')
            ->middleware(['api', 'auth:api'])
            ->group(base_path('routes/authApi.php'));
    }

    /**
     * Define the "auth:api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapTestMailRoutes()
    {
        if (! App::environment('local')) {
            return;
        }
        Route::prefix('mail')
            ->group(base_path('routes/mail.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
