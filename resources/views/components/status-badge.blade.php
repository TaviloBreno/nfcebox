@props([
    'type' => 'default', // payment, status, default
    'value' => '',
    'variant' => null, // override automatic variant
    'size' => 'normal' // sm, normal, lg
])

@php
    // Define badges for payment methods
    $paymentBadges = [
        'dinheiro' => ['variant' => 'success', 'label' => 'Dinheiro'],
        'cartao_credito' => ['variant' => 'primary', 'label' => 'Cartão Crédito'],
        'cartao_debito' => ['variant' => 'info', 'label' => 'Cartão Débito'],
        'pix' => ['variant' => 'warning', 'label' => 'PIX'],
        'boleto' => ['variant' => 'secondary', 'label' => 'Boleto'],
        'transferencia' => ['variant' => 'dark', 'label' => 'Transferência'],
        'cheque' => ['variant' => 'light', 'label' => 'Cheque']
    ];
    
    // Define badges for status
    $statusBadges = [
        'draft' => ['variant' => 'secondary', 'label' => 'Rascunho'],
        'authorized_pending' => ['variant' => 'warning', 'label' => 'Pendente'],
        'authorized' => ['variant' => 'success', 'label' => 'Autorizada'],
        'canceled' => ['variant' => 'danger', 'label' => 'Cancelada'],
        'active' => ['variant' => 'success', 'label' => 'Ativo'],
        'inactive' => ['variant' => 'secondary', 'label' => 'Inativo'],
        'pending' => ['variant' => 'warning', 'label' => 'Pendente'],
        'completed' => ['variant' => 'success', 'label' => 'Concluído'],
        'failed' => ['variant' => 'danger', 'label' => 'Falhou']
    ];
    
    // Determine badge configuration
    $badgeConfig = null;
    
    if ($type === 'payment' && isset($paymentBadges[$value])) {
        $badgeConfig = $paymentBadges[$value];
    } elseif ($type === 'status' && isset($statusBadges[$value])) {
        $badgeConfig = $statusBadges[$value];
    }
    
    // Use provided variant or fallback to default
    $badgeVariant = $variant ?? ($badgeConfig['variant'] ?? 'light');
    $badgeLabel = $badgeConfig['label'] ?? ucfirst(str_replace('_', ' ', $value));
    
    // Size classes
    $sizeClasses = [
        'sm' => 'badge-sm',
        'normal' => '',
        'lg' => 'badge-lg'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? '';
    
    // Variant classes
    $variantClass = match($badgeVariant) {
        'primary' => 'bg-primary',
        'secondary' => 'bg-secondary',
        'success' => 'bg-success',
        'danger' => 'bg-danger',
        'warning' => 'bg-warning',
        'info' => 'bg-info',
        'light' => 'bg-light text-dark',
        'dark' => 'bg-dark',
        default => 'bg-light text-dark'
    };
    
    $classes = trim("badge {$variantClass} {$sizeClass}");
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $badgeLabel }}
</span>