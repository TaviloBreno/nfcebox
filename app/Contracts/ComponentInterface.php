<?php

namespace App\Contracts;

/**
 * Component Interface
 * 
 * Follows Dependency Inversion Principle (DIP) - SOLID
 * Defines contract for all UI components
 */
interface ComponentInterface
{
    /**
     * Render the component
     * 
     * @return string
     */
    public function render(): string;

    /**
     * Get component attributes
     * 
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Set component attributes
     * 
     * @param array $attributes
     * @return self
     */
    public function setAttributes(array $attributes): self;

    /**
     * Validate component data
     * 
     * @return bool
     */
    public function validate(): bool;

    /**
     * Get component configuration
     * 
     * @return array
     */
    public function getConfig(): array;
}