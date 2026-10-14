<?php

namespace App\Providers;

use App\Database\CaseInsensitivePostgresGrammar;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\Payments\PaymentGateway::class, \App\Services\Payments\SelcomGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // PostgreSQL: make the app's LIKE searches case-insensitive, as they are on SQLite/MySQL
        if (config('database.default') === 'pgsql') {
            $connection = DB::connection();
            $connection->setQueryGrammar(new CaseInsensitivePostgresGrammar($connection));
        }
    }
}
