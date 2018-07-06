<?php

namespace App\Providers;

use App\Models\Chapter;
use App\Models\Element;
use App\Models\Section;
use App\Models\Text;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot()
    {
        parent::boot();
        $this->bindTextByKey();
        $this->bindChapterById();
        $this->bindSectionById();
        $this->bindElementById();
    }

    /**
     * Define the routes for the application.
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapAuthorizedApiRoutes();

        $this->mapWebRoutes();
        $this->mapAppRoutes();
    }

    protected function bindTextByKey()
    {
        Route::bind('text', function ($value) {
            return Text::where('key', '=', $value)->first() ?? abort(404);
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
             ->namespace($this->namespace)
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
             ->namespace($this->namespace)
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
            ->namespace($this->namespace)
            ->group(base_path('routes/authApi.php'));
    }
}
