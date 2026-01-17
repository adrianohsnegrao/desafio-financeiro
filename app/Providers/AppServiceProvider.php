<?php

namespace App\Providers;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use App\Services\FakeAuthorizeTransferService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AuthorizeTransferServiceInterface::class,
            FakeAuthorizeTransferService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
