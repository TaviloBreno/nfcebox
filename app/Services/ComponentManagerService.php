<?php

namespace App\Services;

use App\Contracts\ComponentInterface;
use Illuminate\Support\Collection;

/**
 * Component Manager Service
 * 
 * Follows Dependency Inversion Principle (DIP) - SOLID
 * Depends on ComponentInterface abstraction, not concrete implementations
 */
class ComponentManagerService
{
    /**
     * Registered components
     * 
     * @var Collection
     */
    protected Collection $components;

    /**
     * Component configurations
     * 
     * @var array
     */
    protected array $configurations = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->components = collect();
    }

    /**
     * Register a component
     * 
     * @param string $name
     * @param ComponentInterface $component
     * @param array $config
     * @return self
     */
    public function register(string $name, ComponentInterface $component, array $config = []): self
    {
        $this->components->put($name, $component);
        $this->configurations[$name] = $config;
        
        return $this;
    }

    /**
     * Get a component by name
     * 
     * @param string $name
     * @return ComponentInterface|null
     */
    public function get(string $name): ?ComponentInterface
    {
        return $this->components->get($name);
    }

    /**
     * Check if component exists
     * 
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->components->has($name);
    }

    /**
     * Get all registered components
     * 
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->components;
    }

    /**
     * Get component configuration
     * 
     * @param string $name
     * @return array
     */
    public function getConfig(string $name): array
    {
        return $this->configurations[$name] ?? [];
    }

    /**
     * Set component configuration
     * 
     * @param string $name
     * @param array $config
     * @return self
     */
    public function setConfig(string $name, array $config): self
    {
        if ($this->has($name)) {
            $this->configurations[$name] = array_merge(
                $this->configurations[$name] ?? [],
                $config
            );
        }
        
        return $this;
    }

    /**
     * Create component instance with attributes
     * 
     * @param string $name
     * @param array $attributes
     * @return ComponentInterface|null
     */
    public function create(string $name, array $attributes = []): ?ComponentInterface
    {
        $component = $this->get($name);
        
        if (!$component) {
            return null;
        }
        
        // Clone the component to avoid modifying the original
        $instance = clone $component;
        $instance->setAttributes($attributes);
        
        // Apply configuration
        $config = $this->getConfig($name);
        if (!empty($config)) {
            $instance->setConfig($config);
        }
        
        return $instance;
    }

    /**
     * Render component
     * 
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public function render(string $name, array $attributes = []): string
    {
        $component = $this->create($name, $attributes);
        
        if (!$component) {
            return "<!-- Component '{$name}' not found -->";
        }
        
        if (!$component->validate()) {
            return "<!-- Component '{$name}' validation failed -->";
        }
        
        return $component->render();
    }

    /**
     * Validate component
     * 
     * @param string $name
     * @param array $attributes
     * @return bool
     */
    public function validate(string $name, array $attributes = []): bool
    {
        $component = $this->create($name, $attributes);
        
        if (!$component) {
            return false;
        }
        
        return $component->validate();
    }

    /**
     * Get component names by category
     * 
     * @param string $category
     * @return array
     */
    public function getByCategory(string $category): array
    {
        return $this->components
            ->filter(function ($component, $name) use ($category) {
                $config = $this->getConfig($name);
                return ($config['category'] ?? '') === $category;
            })
            ->keys()
            ->toArray();
    }

    /**
     * Register multiple components
     * 
     * @param array $components
     * @return self
     */
    public function registerMany(array $components): self
    {
        foreach ($components as $name => $data) {
            if (is_array($data) && isset($data['component'])) {
                $this->register(
                    $name,
                    $data['component'],
                    $data['config'] ?? []
                );
            } elseif ($data instanceof ComponentInterface) {
                $this->register($name, $data);
            }
        }
        
        return $this;
    }

    /**
     * Unregister a component
     * 
     * @param string $name
     * @return self
     */
    public function unregister(string $name): self
    {
        $this->components->forget($name);
        unset($this->configurations[$name]);
        
        return $this;
    }

    /**
     * Clear all components
     * 
     * @return self
     */
    public function clear(): self
    {
        $this->components = collect();
        $this->configurations = [];
        
        return $this;
    }

    /**
     * Get component statistics
     * 
     * @return array
     */
    public function getStats(): array
    {
        $categories = [];
        
        foreach ($this->configurations as $name => $config) {
            $category = $config['category'] ?? 'uncategorized';
            $categories[$category] = ($categories[$category] ?? 0) + 1;
        }
        
        return [
            'total' => $this->components->count(),
            'categories' => $categories,
            'names' => $this->components->keys()->toArray()
        ];
    }

    /**
     * Export component configurations
     * 
     * @return array
     */
    public function exportConfigurations(): array
    {
        return $this->configurations;
    }

    /**
     * Import component configurations
     * 
     * @param array $configurations
     * @return self
     */
    public function importConfigurations(array $configurations): self
    {
        $this->configurations = array_merge($this->configurations, $configurations);
        
        return $this;
    }

    /**
     * Batch render components
     * 
     * @param array $components
     * @return array
     */
    public function batchRender(array $components): array
    {
        $results = [];
        
        foreach ($components as $name => $attributes) {
            $results[$name] = $this->render($name, $attributes);
        }
        
        return $results;
    }

    /**
     * Get component dependencies
     * 
     * @param string $name
     * @return array
     */
    public function getDependencies(string $name): array
    {
        $config = $this->getConfig($name);
        return $config['dependencies'] ?? [];
    }

    /**
     * Check if component has dependencies
     * 
     * @param string $name
     * @return bool
     */
    public function hasDependencies(string $name): bool
    {
        return !empty($this->getDependencies($name));
    }
}