<?php

namespace App\Providers;

use App\Models\Expense;
use App\Observers\ExpenseObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Passport::loadKeysFrom(base_path(config('passport.key_path')));
        Expense::observe(ExpenseObserver::class);
    }
}
