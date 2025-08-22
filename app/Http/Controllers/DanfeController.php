<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\Fiscal\DanfeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class DanfeController extends Controller
{
    protected $danfeService;

    public function __construct(DanfeService $danfeService)
    {
        $this->danfeService = $danfeService;
    }

    /**
     * Gera e baixa o DANFE em PDF
     */
    public function download(Sale $sale)
    {
        try {
            // Verifica se a venda está autorizada
            if ($sale->status !== 'authorized') {
                return response()->json([
                    'error' => 'Venda não autorizada. Não é possível gerar DANFE.'
                ], 400);
            }

            // Gera o PDF
            $pdfContent = $this->danfeService->generateDanfe($sale);
            
            $filename = "danfe_nfce_{$sale->id}.pdf";
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro ao gerar DANFE: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Visualiza o DANFE no navegador (para impressão)
     */
    public function print(Sale $sale)
    {
        try {
            // Verifica se a venda está autorizada
            if ($sale->status !== 'authorized') {
                return response()->json([
                    'error' => 'Venda não autorizada. Não é possível gerar DANFE.'
                ], 400);
            }

            // Gera o PDF
            $pdfContent = $this->danfeService->generateDanfe($sale);
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro ao gerar DANFE: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna informações da NFCe para preview
     */
    public function info(Sale $sale)
    {
        try {
            if ($sale->status !== 'authorized') {
                return response()->json([
                    'error' => 'Venda não autorizada.'
                ], 400);
            }

            return response()->json([
                'sale_id' => $sale->id,
                'number' => $sale->number,
                'access_key' => $sale->nfce_key,
                'protocol' => $sale->protocol,
                'authorized_at' => $sale->authorized_at,
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
                'customer' => $sale->customer,
                'items_count' => $sale->items()->count(),
                'xml_path' => $sale->xml_path
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro ao obter informações: ' . $e->getMessage()
            ], 500);
        }
    }
}
