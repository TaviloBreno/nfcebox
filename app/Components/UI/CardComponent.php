<?php

namespace App\Components\UI;

use App\Components\BaseComponent;

/**
 * Card Component Class
 * 
 * Follows SOLID principles:
 * - SRP: Responsible only for card rendering
 * - OCP: Open for extension, closed for modification
 * - LSP: Can substitute BaseComponent
 * - ISP: Implements only needed interfaces
 * - DIP: Depends on abstractions
 */
class CardComponent extends BaseComponent
{
    /**
     * Default attributes
     * 
     * @var array
     */
    protected array $defaultAttributes = [
        'shadow' => true,
        'border' => false,
        'rounded' => true,
        'hoverable' => false
    ];

    /**
     * Valid card variants
     * 
     * @var array
     */
    protected array $validVariants = [
        'default', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'
    ];

    /**
     * Get default configuration
     * 
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        return [
            'allowedVariants' => $this->validVariants,
            'defaultHeaderClass' => 'card-header',
            'defaultBodyClass' => 'card-body',
            'defaultFooterClass' => 'card-footer'
        ];
    }

    /**
     * Custom validation logic
     * 
     * @return bool
     */
    protected function customValidation(): bool
    {
        $variant = $this->getAttribute('variant', 'default');
        
        if (!in_array($variant, $this->validVariants)) {
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
        $classes = ['card'];

        if ($this->getAttribute('shadow')) {
            $classes[] = 'shadow-sm';
        }

        if ($this->getAttribute('border')) {
            $classes[] = 'border';
        }

        if ($this->getAttribute('rounded')) {
            $classes[] = 'rounded';
        }

        if ($this->getAttribute('hoverable')) {
            $classes[] = 'card-hoverable';
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
        $variant = $this->getAttribute('variant', 'default');
        
        if ($variant === 'default') {
            return [];
        }

        return ["card-{$variant}"];
    }

    /**
     * Get card header content
     * 
     * @return string|null
     */
    public function getHeaderContent(): ?string
    {
        $title = $this->getAttribute('title');
        $icon = $this->getAttribute('icon');
        $headerActions = $this->getAttribute('headerActions');
        
        if (!$title && !$icon && !$headerActions) {
            return null;
        }

        $content = '';
        
        // Title section
        if ($title || $icon) {
            $content .= '<div class="card-title-section">';
            
            if ($icon) {
                $content .= "<i class=\"{$icon} me-2\"></i>";
            }
            
            if ($title) {
                $content .= "<h5 class=\"card-title mb-0\">{$this->sanitizeContent($title)}</h5>";
            }
            
            $content .= '</div>';
        }
        
        // Header actions
        if ($headerActions) {
            $content .= '<div class="card-header-actions">';
            $content .= $headerActions;
            $content .= '</div>';
        }

        return $content;
    }

    /**
     * Get card body content
     * 
     * @return string
     */
    public function getBodyContent(): string
    {
        return $this->getAttribute('body', $this->slot ?? '');
    }

    /**
     * Get card footer content
     * 
     * @return string|null
     */
    public function getFooterContent(): ?string
    {
        return $this->getAttribute('footer');
    }

    /**
     * Get header CSS classes
     * 
     * @return string
     */
    public function getHeaderClasses(): string
    {
        $defaultClass = $this->config['defaultHeaderClass'];
        $customClass = $this->getAttribute('headerClass', '');
        
        $classes = array_filter([$defaultClass, $customClass]);
        
        return implode(' ', $classes);
    }

    /**
     * Get body CSS classes
     * 
     * @return string
     */
    public function getBodyClasses(): string
    {
        $defaultClass = $this->config['defaultBodyClass'];
        $customClass = $this->getAttribute('bodyClass', '');
        
        $classes = array_filter([$defaultClass, $customClass]);
        
        return implode(' ', $classes);
    }

    /**
     * Get footer CSS classes
     * 
     * @return string
     */
    public function getFooterClasses(): string
    {
        $defaultClass = $this->config['defaultFooterClass'];
        $customClass = $this->getAttribute('footerClass', '');
        
        $classes = array_filter([$defaultClass, $customClass]);
        
        return implode(' ', $classes);
    }

    /**
     * Check if card has header
     * 
     * @return bool
     */
    public function hasHeader(): bool
    {
        return !is_null($this->getHeaderContent());
    }

    /**
     * Check if card has footer
     * 
     * @return bool
     */
    public function hasFooter(): bool
    {
        return !is_null($this->getFooterContent());
    }

    /**
     * Render the component
     * 
     * @return string
     */
    public function render(): string
    {
        if (!$this->validate()) {
            return '<!-- Invalid card component -->';
        }

        $classes = $this->generateClasses();
        $attributesString = $this->generateAttributesString(['title', 'icon', 'headerActions', 'body', 'footer', 'headerClass', 'bodyClass', 'footerClass', 'shadow', 'border', 'rounded', 'hoverable', 'variant']);
        
        $html = "<div class=\"{$classes}\" {$attributesString}>";
        
        // Header
        if ($this->hasHeader()) {
            $headerClasses = $this->getHeaderClasses();
            $headerContent = $this->getHeaderContent();
            $html .= "<div class=\"{$headerClasses}\">{$headerContent}</div>";
        }
        
        // Body
        $bodyClasses = $this->getBodyClasses();
        $bodyContent = $this->getBodyContent();
        $html .= "<div class=\"{$bodyClasses}\">{$bodyContent}</div>";
        
        // Footer
        if ($this->hasFooter()) {
            $footerClasses = $this->getFooterClasses();
            $footerContent = $this->getFooterContent();
            $html .= "<div class=\"{$footerClasses}\">{$footerContent}</div>";
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function view()
    {
        return 'components.ui.card';
    }
}