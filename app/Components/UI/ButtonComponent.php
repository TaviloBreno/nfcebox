<?php

namespace App\Components\UI;

use App\Components\BaseComponent;

/**
 * Button Component Class
 * 
 * Follows SOLID principles:
 * - SRP: Responsible only for button rendering
 * - OCP: Open for extension, closed for modification
 * - LSP: Can substitute BaseComponent
 * - ISP: Implements only needed interfaces
 * - DIP: Depends on abstractions
 */
class ButtonComponent extends BaseComponent
{
    /**
     * Default attributes
     * 
     * @var array
     */
    protected array $defaultAttributes = [
        'type' => 'button',
        'variant' => 'primary',
        'size' => 'md',
        'disabled' => false,
        'loading' => false,
        'iconPosition' => 'left'
    ];

    /**
     * Valid button variants
     * 
     * @var array
     */
    protected array $validVariants = [
        'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark',
        'outline-primary', 'outline-secondary', 'outline-success', 'outline-danger',
        'outline-warning', 'outline-info', 'outline-light', 'outline-dark',
        'gradient-primary', 'gradient-success'
    ];

    /**
     * Valid button sizes
     * 
     * @var array
     */
    protected array $validSizes = ['sm', 'md', 'lg'];

    /**
     * Get default configuration
     * 
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        return [
            'allowedVariants' => $this->validVariants,
            'allowedSizes' => $this->validSizes,
            'defaultIcon' => null,
            'loadingText' => 'Carregando...',
            'loadingIcon' => 'fas fa-spinner fa-spin'
        ];
    }

    /**
     * Custom validation logic
     * 
     * @return bool
     */
    protected function customValidation(): bool
    {
        $variant = $this->getAttribute('variant');
        $size = $this->getAttribute('size');

        if (!in_array($variant, $this->validVariants)) {
            return false;
        }

        if (!in_array($size, $this->validSizes)) {
            return false;
        }

        return true;
    }

    /**
     * Get base CSS classes
     * 
     * @return array
     */
    protected function getBaseClasses(): array
    {
        $classes = ['btn'];

        if ($this->getAttribute('loading')) {
            $classes[] = 'btn-loading';
        }

        return $classes;
    }

    /**
     * Get variant CSS classes
     * 
     * @return array
     */
    protected function getVariantClasses(): array
    {
        $variant = $this->getAttribute('variant');
        return ["btn-{$variant}"];
    }

    /**
     * Get size CSS classes
     * 
     * @return array
     */
    protected function getSizeClasses(): array
    {
        $size = $this->getAttribute('size');
        
        if ($size === 'md') {
            return []; // Default size, no class needed
        }

        return ["btn-{$size}"];
    }

    /**
     * Get button content with icon
     * 
     * @return string
     */
    public function getButtonContent(): string
    {
        $content = '';
        $icon = $this->getAttribute('icon');
        $iconPosition = $this->getAttribute('iconPosition', 'left');
        $text = $this->getAttribute('text', $this->slot ?? '');
        $loading = $this->getAttribute('loading');

        if ($loading) {
            $loadingIcon = $this->config['loadingIcon'];
            $loadingText = $this->config['loadingText'];
            
            $content = "<i class=\"{$loadingIcon} me-2\"></i>{$loadingText}";
        } else {
            if ($icon && $iconPosition === 'left') {
                $content .= "<i class=\"{$icon} me-2\"></i>";
            }

            $content .= $this->sanitizeContent($text);

            if ($icon && $iconPosition === 'right') {
                $content .= "<i class=\"{$icon} ms-2\"></i>";
            }
        }

        return $content;
    }

    /**
     * Get button tag
     * 
     * @return string
     */
    public function getButtonTag(): string
    {
        return $this->hasAttribute('href') ? 'a' : 'button';
    }

    /**
     * Get button attributes for rendering
     * 
     * @return array
     */
    public function getButtonAttributes(): array
    {
        $attributes = [];
        $tag = $this->getButtonTag();

        if ($tag === 'button') {
            $attributes['type'] = $this->getAttribute('type', 'button');
            
            if ($this->getAttribute('disabled') || $this->getAttribute('loading')) {
                $attributes['disabled'] = true;
            }
        } else {
            $attributes['href'] = $this->getAttribute('href');
            $attributes['target'] = $this->getAttribute('target');
            
            if ($this->getAttribute('disabled') || $this->getAttribute('loading')) {
                $attributes['class'] = ($attributes['class'] ?? '') . ' disabled';
                $attributes['aria-disabled'] = 'true';
            }
        }

        // Add data attributes
        foreach ($this->attributes as $key => $value) {
            if (strpos($key, 'data-') === 0) {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Render the component
     * 
     * @return string
     */
    public function render(): string
    {
        if (!$this->validate()) {
            return '<!-- Invalid button component -->';
        }

        $tag = $this->getButtonTag();
        $classes = $this->generateClasses();
        $content = $this->getButtonContent();
        $attributes = $this->getButtonAttributes();
        
        // Build attributes string
        $attributesString = "class=\"{$classes}\"";
        foreach ($attributes as $key => $value) {
            if ($key !== 'class' && !is_null($value)) {
                if (is_bool($value)) {
                    if ($value) {
                        $attributesString .= " {$key}";
                    }
                } else {
                    $attributesString .= " {$key}=\"" . htmlspecialchars($value) . "\"";
                }
            }
        }

        return "<{$tag} {$attributesString}>{$content}</{$tag}>";
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function view()
    {
        return 'components.ui.button';
    }
}