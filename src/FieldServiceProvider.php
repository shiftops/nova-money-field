<?php

namespace Vyuldashev\NovaMoneyField;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Vyuldashev\NovaMoneyField\Http\Controllers\MoneyInlineUpdateController;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(static function (ServingNova $event) {
            Nova::script('nova-money-field', __DIR__.'/../dist/js/field.js');
        });

        $this->app->booted(function (): void {
            $this->routes();
        });
    }

    /**
     * @return void
     */
    public function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->group(function (): void {
                Route::post('nova-money-field/update/{resource}', MoneyInlineUpdateController::class);
            });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
