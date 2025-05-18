<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Product\ProductRepositoryInterface;
use App\Domain\Product\Repositories\SQLiteProductRepository;
use App\Domain\Product\Repositories\MongoProductRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('testing')) {
            $this->app->bind(ProductRepositoryInterface::class, SQLiteProductRepository::class);
        } else {
            $this->app->bind(ProductRepositoryInterface::class, MongoProductRepository::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
