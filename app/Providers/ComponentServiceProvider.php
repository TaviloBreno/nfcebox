<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\ComponentManagerService;
use App\Services\ValidationService;
use App\Components\UI\ButtonComponent;
use App\Components\UI\CardComponent;

/**
 * Component Service Provider
 * 
 * Follows Single Responsibility Principle (SRP) - SOLID
 * Responsible only for registering Blade components and services
 */
class ComponentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register ComponentManagerService as singleton
        $this->app->singleton(ComponentManagerService::class, function ($app) {
            return new ComponentManagerService();
        });

        // Register ValidationService as singleton
        $this->app->singleton(ValidationService::class, function ($app) {
            return new ValidationService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerBladeComponents();
        $this->registerComponentInstances();
    }

    /**
     * Register Blade components
     * 
     * @return void
     */
    protected function registerBladeComponents(): void
    {
        // UI Components
        Blade::component('components.ui.card', 'card');
        Blade::component('components.ui.button', 'button');
        Blade::component('components.ui.modal', 'modal');
        Blade::component('components.ui.table', 'table');

        // Form Components
        Blade::component('components.forms.input', 'input');
        Blade::component('components.forms.select', 'select');

        // Layout Components (placeholder)
        // Blade::component('components.layout.header', 'header');
        // Blade::component('components.layout.footer', 'footer');
        // Blade::component('components.layout.sidebar', 'sidebar');
    }

    /**
     * Register component instances in ComponentManager
     * 
     * @return void
     */
    protected function registerComponentInstances(): void
    {
        $componentManager = $this->app->make(ComponentManagerService::class);

        // Register UI components
        $componentManager->registerMany([
            'button' => [
                'component' => new ButtonComponent(),
                'config' => [
                    'category' => 'ui',
                    'description' => 'Reusable button component with multiple variants',
                    'dependencies' => ['bootstrap']
                ]
            ],
            'card' => [
                'component' => new CardComponent(),
                'config' => [
                    'category' => 'ui',
                    'description' => 'Flexible card component for content display',
                    'dependencies' => ['bootstrap']
                ]
            ]
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            ComponentManagerService::class,
            ValidationService::class,
        ];
    }
}