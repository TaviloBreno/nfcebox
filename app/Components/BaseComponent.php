<?php

namespace App\Components;

use App\Contracts\ComponentInterface;
use Illuminate\View\Component;

/**
 * Base Component Class
 * 
 * Follows Liskov Substitution Principle (LSP) - SOLID
 * Abstract base class that can be substituted by any concrete component
 */
abstract class BaseComponent extends Component implements ComponentInterface
{
    /**
     * Component attributes
     * 
     * @var array
     */
    protected array $attributes = [];

    /**
     * Component configuration
     * 
     * @var array
     */
    protected array $config = [];

    /**
     * Default attributes
     * 
     * @var array
     */
    protected array $defaultAttributes = [];

    /**
     * Required attributes
     * 
     * @var array
     */
    protected array $requiredAttributes = [];

    /**
     * Constructor
     * 
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = array_merge($this->defaultAttributes, $attributes);
        $this->config = $this->getDefaultConfig();
    }

    /**
     * Get component attributes
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set component attributes
     * 
     * @param array $attributes
     * @return self
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Get single attribute
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Set single attribute
     * 
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get component configuration
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set component configuration
     * 
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Validate component data
     * 
     * @return bool
     */
    public function validate(): bool
    {
        // Check required attributes
        foreach ($this->requiredAttributes as $required) {
            if (!isset($this->attributes[$required]) || empty($this->attributes[$required])) {
                return false;
            }
        }

        return $this->customValidation();
    }

    /**
     * Custom validation logic (to be overridden by child classes)
     * 
     * @return bool
     */
    protected function customValidation(): bool
    {
        return true;
    }

    /**
     * Get default configuration (to be overridden by child classes)
     * 
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        return [];
    }

    /**
     * Generate CSS classes
     * 
     * @param array $additionalClasses
     * @return string
     */
    protected function generateClasses(array $additionalClasses = []): string
    {
        $classes = array_merge(
            $this->getBaseClasses(),
            $this->getVariantClasses(),
            $this->getSizeClasses(),
            $additionalClasses,
            explode(' ', $this->getAttribute('class', ''))
        );

        return implode(' ', array_filter($classes));
    }

    /**
     * Get base CSS classes (to be overridden by child classes)
     * 
     * @return array
     */
    protected function getBaseClasses(): array
    {
        return [];
    }

    /**
     * Get variant CSS classes (to be overridden by child classes)
     * 
     * @return array
     */
    protected function getVariantClasses(): array
    {
        return [];
    }

    /**
     * Get size CSS classes (to be overridden by child classes)
     * 
     * @return array
     */
    protected function getSizeClasses(): array
    {
        return [];
    }

    /**
     * Generate HTML attributes string
     * 
     * @param array $excludeAttributes
     * @return string
     */
    protected function generateAttributesString(array $excludeAttributes = []): string
    {
        $attributes = [];
        $exclude = array_merge(['class'], $excludeAttributes);

        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $exclude) && !is_null($value)) {
                if (is_bool($value)) {
                    if ($value) {
                        $attributes[] = $key;
                    }
                } else {
                    $attributes[] = sprintf('%s="%s"', $key, htmlspecialchars($value));
                }
            }
        }

        return implode(' ', $attributes);
    }

    /**
     * Sanitize HTML content
     * 
     * @param string $content
     * @return string
     */
    protected function sanitizeContent(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if attribute exists and is not empty
     * 
     * @param string $key
     * @return bool
     */
    protected function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]) && !empty($this->attributes[$key]);
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    abstract public function render();
}