<?php

namespace App\Providers;

use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use App\Repositories\Contracts\RFPsPlatformRepositoryInterface;
use App\Repositories\Eloquent\InstitutionRepository;
use App\Repositories\Eloquent\LocationSearchRepository;
use App\Repositories\Eloquent\RFPsPlatformRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InstitutionRepositoryInterface::class, InstitutionRepository::class);
        $this->app->bind(LocationSearchRepositoryInterface::class, LocationSearchRepository::class);
        $this->app->bind(RFPsPlatformRepositoryInterface::class, RFPsPlatformRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
