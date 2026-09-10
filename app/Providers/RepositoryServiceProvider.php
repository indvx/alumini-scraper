<?php

namespace App\Providers;

use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use App\Repositories\Eloquent\InstitutionRepository;
use App\Repositories\Eloquent\LocationSearchRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InstitutionRepositoryInterface::class, InstitutionRepository::class);
        $this->app->bind(LocationSearchRepositoryInterface::class, LocationSearchRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
