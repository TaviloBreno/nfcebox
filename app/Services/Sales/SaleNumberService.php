<?php

namespace App\Services\Sales;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleNumberService
{
    /**
     * Gera o próximo número sequencial para uma venda.
     * Utiliza lock transacional para evitar problemas de concorrência.
     *
     * @return string
     * @throws \Exception
     */
    public function generateNextNumber(): string
    {
        return DB::transaction(function () {
            // Busca o último número sequencial com lock pessimista
            $lastSale = Sale::lockForUpdate()
                ->whereNotNull('number')
                ->where('number', 'LIKE', 'VENDA-%')
                ->orderByRaw('CAST(SUBSTRING(number, 7) AS UNSIGNED) DESC')
                ->first();
            
            $nextNumber = 1;
            
            if ($lastSale && $lastSale->number) {
                // Extrai o número da string (formato: VENDA-000001)
                $lastNumber = (int) substr($lastSale->number, 6);
                $nextNumber = $lastNumber + 1;
            }
            
            // Formata o número com 6 dígitos
            $formattedNumber = 'VENDA-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
            Log::info('Número de venda gerado', [
                'number' => $formattedNumber,
                'last_number' => $lastSale?->number ?? 'N/A',
                'next_sequence' => $nextNumber
            ]);
            
            return $formattedNumber;
        });
    }
    
    /**
     * Verifica se um número de venda já existe.
     *
     * @param string $number
     * @return bool
     */
    public function numberExists(string $number): bool
    {
        return Sale::where('number', $number)->exists();
    }
    
    /**
     * Gera um número único garantindo que não existe duplicata.
     * Método alternativo usando retry em caso de conflito.
     *
     * @param int $maxRetries
     * @return string
     * @throws \Exception
     */
    public function generateUniqueNumber(int $maxRetries = 3): string
    {
        $attempts = 0;
        
        while ($attempts < $maxRetries) {
            try {
                $number = $this->generateNextNumber();
                
                // Verifica se o número já existe (double-check)
                if (!$this->numberExists($number)) {
                    return $number;
                }
                
                Log::warning('Número de venda duplicado detectado', [
                    'number' => $number,
                    'attempt' => $attempts + 1
                ]);
                
            } catch (\Exception $e) {
                Log::error('Erro ao gerar número de venda', [
                    'attempt' => $attempts + 1,
                    'error' => $e->getMessage()
                ]);
            }
            
            $attempts++;
            
            // Pequeno delay antes de tentar novamente
            if ($attempts < $maxRetries) {
                usleep(100000); // 100ms
            }
        }
        
        throw new \Exception('Não foi possível gerar um número único de venda após ' . $maxRetries . ' tentativas.');
    }
}