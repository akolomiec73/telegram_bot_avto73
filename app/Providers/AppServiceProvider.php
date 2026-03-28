<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\CacheRepository;
use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\Contracts\DatabaseRepositoryInterface;
use App\Repositories\DatabaseRepository;
use App\Services\SenderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            DatabaseRepositoryInterface::class,
            DatabaseRepository::class
        );
        $this->app->bind(
            CacheRepositoryInterface::class,
            CacheRepository::class
        );
        $this->app->when(SenderService::class)
            ->needs('$publicGroupId')
            ->give(config('services.telegram.public_group_id'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
