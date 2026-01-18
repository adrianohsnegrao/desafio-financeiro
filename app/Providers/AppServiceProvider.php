<?php

namespace App\Providers;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use App\Domains\Transfer\Contracts\NotifyTransferServiceInterface;
use App\Domains\Transfer\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Persistence\EloquentTransferRepository;
use App\Services\FakeAuthorizeTransferService;
use App\Services\FakeNotifyTransferService;
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

        $this->app->bind(
            NotifyTransferServiceInterface::class,
            FakeNotifyTransferService::class
        );

        $this->app->bind(
            TransferRepositoryInterface::class,
            EloquentTransferRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
