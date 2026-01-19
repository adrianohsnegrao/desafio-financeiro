<?php

namespace App\Providers;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use App\Domains\Transfer\Contracts\NotifyTransferServiceInterface;
use App\Domains\Transfer\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Persistence\EloquentTransferRepository;
use App\Infrastructure\Services\AuthorizeTransferService;
use App\Infrastructure\Services\NotifyTransferService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TransferRepositoryInterface::class,
            EloquentTransferRepository::class
        );

        $this->app->bind(
            AuthorizeTransferServiceInterface::class,
            AuthorizeTransferService::class
        );

        $this->app->bind(
            NotifyTransferServiceInterface::class,
            NotifyTransferService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
