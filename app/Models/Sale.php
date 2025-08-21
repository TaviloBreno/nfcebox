<?php

namespace App\Models;

use App\Services\Sales\SaleNumberService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Sale extends Model
{
    protected $fillable = [
        'number',
        'customer_id',
        'total',
        'payment_method',
        'status',
        'nfce_key',
        'protocol',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];
    
    /**
     * Boot do modelo para configurar eventos.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Evento antes de criar uma venda
        static::creating(function ($sale) {
            // Se não tem número e o status é 'authorized', gera o número
            if (empty($sale->number) && $sale->status === 'authorized') {
                $sale->generateSaleNumber();
            }
        });
        
        // Evento antes de atualizar uma venda
        static::updating(function ($sale) {
            // Se mudou para 'authorized' e não tem número, gera o número
            if (empty($sale->number) && $sale->status === 'authorized' && $sale->isDirty('status')) {
                $sale->generateSaleNumber();
            }
        });
    }

    /**
     * Get the customer that owns the sale.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sale items for the sale.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    
    /**
     * Gera o número sequencial da venda usando o serviço.
     *
     * @return void
     * @throws \Exception
     */
    public function generateSaleNumber(): void
    {
        try {
            $saleNumberService = new SaleNumberService();
            $this->number = $saleNumberService->generateUniqueNumber();
            
            Log::info('Número de venda gerado para Sale', [
                'sale_id' => $this->id ?? 'novo',
                'number' => $this->number
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar número da venda', [
                'sale_id' => $this->id ?? 'novo',
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Confirma a venda alterando o status para 'authorized' e gerando o número.
     *
     * @return bool
     * @throws \Exception
     */
    public function confirm(): bool
    {
        if ($this->status === 'authorized') {
            return true; // Já está confirmada
        }
        
        if ($this->status === 'canceled') {
            throw new \Exception('Não é possível confirmar uma venda cancelada.');
        }
        
        $this->status = 'authorized';
        
        // O número será gerado automaticamente pelo evento 'updating'
        return $this->save();
    }
    
    /**
     * Cancela a venda.
     *
     * @return bool
     */
    public function cancel(): bool
    {
        if ($this->status === 'canceled') {
            return true; // Já está cancelada
        }
        
        $this->status = 'canceled';
        
        Log::info('Venda cancelada', [
            'sale_id' => $this->id,
            'number' => $this->number
        ]);
        
        return $this->save();
    }
    
    /**
     * Verifica se a venda está confirmada.
     *
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'authorized';
    }
    
    /**
     * Verifica se a venda está cancelada.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }
    
    /**
     * Verifica se a venda é um rascunho.
     *
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
