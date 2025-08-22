<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Customer::class => \App\Policies\CustomerPolicy::class,
        \App\Models\Product::class => \App\Policies\ProductPolicy::class,
        \App\Models\CompanyConfig::class => \App\Policies\CompanyConfigPolicy::class,
        \App\Models\Sale::class => \App\Policies\SalePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates para controle de acesso baseado em roles
        Gate::define('access-admin', function (User $user) {
            return $user->canAccessAdmin();
        });

        Gate::define('access-pos', function (User $user) {
            return $user->canAccessPos();
        });

        Gate::define('access-fiscal', function (User $user) {
            return $user->canAccessFiscal();
        });

        Gate::define('manage-customers', function (User $user) {
            return $user->canManageCustomers();
        });

        Gate::define('manage-products', function (User $user) {
            return $user->canManageProducts();
        });

        Gate::define('manage-config', function (User $user) {
            return $user->canManageConfig();
        });

        Gate::define('manage-sales', function (User $user) {
            return $user->canManageSales();
        });

        // Gates específicos para roles
        Gate::define('is-admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('is-operador', function (User $user) {
            return $user->isOperador();
        });

        Gate::define('is-fiscal', function (User $user) {
            return $user->isFiscal();
        });
    }
}