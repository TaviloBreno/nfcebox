<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inutilization extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'series',
        'numero_inicial',
        'numero_final',
        'justificativa',
        'protocol',
        'xml_path',
        'status',
        'sefaz_response',
        'sefaz_error_code',
        'sefaz_error_message',
        'authorized_at',
        'retry_count',
        'next_retry_at',
    ];

    protected $casts = [
        'authorized_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'retry_count' => 'integer',
        'numero_inicial' => 'integer',
        'numero_final' => 'integer',
    ];

    /**
     * Relacionamento com o usuário que criou a inutilização
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se a inutilização foi autorizada
     */
    public function isAuthorized(): bool
    {
        return $this->status === 'authorized';
    }

    /**
     * Verifica se a inutilização está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se a inutilização foi rejeitada
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Verifica se houve erro na inutilização
     */
    public function hasError(): bool
    {
        return $this->status === 'error';
    }

    /**
     * Marca a inutilização como autorizada
     */
    public function markAsAuthorized(string $protocol, string $xmlPath = null): void
    {
        $this->update([
            'status' => 'authorized',
            'protocol' => $protocol,
            'xml_path' => $xmlPath,
            'authorized_at' => now(),
        ]);
    }

    /**
     * Marca a inutilização como rejeitada
     */
    public function markAsRejected(string $errorCode = null, string $errorMessage = null): void
    {
        $this->update([
            'status' => 'rejected',
            'sefaz_error_code' => $errorCode,
            'sefaz_error_message' => $errorMessage,
        ]);
    }

    /**
     * Marca a inutilização com erro
     */
    public function markAsError(string $errorCode = null, string $errorMessage = null): void
    {
        $this->update([
            'status' => 'error',
            'sefaz_error_code' => $errorCode,
            'sefaz_error_message' => $errorMessage,
        ]);
    }

    /**
     * Incrementa o contador de tentativas
     */
    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
        
        // Calcula próxima tentativa com backoff exponencial
        $nextRetry = now()->addMinutes(pow(2, $this->retry_count));
        $this->update(['next_retry_at' => $nextRetry]);
    }

    /**
     * Scope para inutilizações que precisam ser reenviadas
     */
    public function scopeNeedsRetry($query)
    {
        return $query->where('status', 'pending')
                    ->where('retry_count', '<', 5)
                    ->where(function ($q) {
                        $q->whereNull('next_retry_at')
                          ->orWhere('next_retry_at', '<=', now());
                    });
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por usuário
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Retorna a quantidade de números inutilizados
     */
    public function getQuantidadeAttribute(): int
    {
        return ($this->numero_final - $this->numero_inicial) + 1;
    }

    /**
     * Retorna a faixa formatada
     */
    public function getFaixaAttribute(): string
    {
        if ($this->numero_inicial === $this->numero_final) {
            return (string) $this->numero_inicial;
        }
        
        return $this->numero_inicial . ' - ' . $this->numero_final;
    }

    /**
     * Retorna o status formatado para exibição
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'authorized' => 'Autorizada',
            'rejected' => 'Rejeitada',
            'error' => 'Erro',
            default => 'Desconhecido'
        };
    }

    /**
     * Retorna a classe CSS para o status
     */
    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'authorized' => 'badge-success',
            'rejected' => 'badge-danger',
            'error' => 'badge-danger',
            default => 'badge-secondary'
        };
    }
}