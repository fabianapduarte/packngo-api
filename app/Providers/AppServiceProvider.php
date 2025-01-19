<?php

namespace App\Providers;

use App\Models\Trip;
use App\Models\User;
use App\Policies\TripPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Trip::class, TripPolicy::class);
    }
}
