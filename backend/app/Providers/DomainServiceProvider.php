<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Ledger\Services\LedgerService;
use App\Domains\Security\Services\AuthSecurityService;
use App\Domains\Security\Services\SecureUploadService;
use Illuminate\Support\ServiceProvider;

final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LedgerService::class);
        $this->app->singleton(AuthSecurityService::class);
        $this->app->singleton(SecureUploadService::class);
    }

    public function boot(): void
    {
        //
    }
}
