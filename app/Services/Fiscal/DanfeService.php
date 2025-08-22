<?php

namespace App\Services\Fiscal;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;

class DanfeService
{
    /**
     * Gera o DANFE em formato PDF para cupom térmico 58mm
     */
    public function generateDanfe(Sale $sale): string
    {
        try {
            // Carrega os relacionamentos necessários
            $sale->load(['items.product', 'customer', 'company']);
            
            // Gera o QR Code com dados da venda
            $qrCodeData = $this->generateQrCodeData($sale);
            $qrCodeSvg = QrCode::format('svg')
                ->size(80)
                ->margin(0)
                ->generate($qrCodeData);
            
            // Dados para o template
            $data = [
                'sale' => $sale,
                'company' => $sale->company,
                'customer' => $sale->customer,
                'items' => $sale->items,
                'qrcode' => $qrCodeSvg,
                'formatted_nfce_number' => $this->formatNfceNumber($sale->number),
                'formatted_access_key' => $this->formatAccessKey($sale->nfce_key),
                'payment_method_label' => $this->getPaymentMethodLabel($sale->payment_method)
            ];
            
            // Configura o PDF para cupom térmico 58mm
            // 58mm = 164.4px a 72dpi (58 * 72 / 25.4)
            // Altura dinâmica baseada no conteúdo
            $pdf = Pdf::loadView('danfe.cupom-58mm', $data)
                ->setPaper([0, 0, 164.4, 2000], 'portrait') // 58mm largura x altura máxima
                ->setOptions([
                    'dpi' => 72,
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false,
                    'fontSubsetting' => false,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                    'debugLayoutLines' => false,
                    'debugLayoutBlocks' => false,
                    'debugLayoutInline' => false,
                    'debugLayoutPaddingBox' => false
                ]);
            
            return $pdf->output();
            
        } catch (Exception $e) {
            throw new Exception('Erro ao gerar DANFE: ' . $e->getMessage());
        }
    }
    
    /**
     * Gera os dados para o QR Code da NFCe
     */
    private function generateQrCodeData(Sale $sale): string
    {
        // URL de consulta da NFCe (formato padrão SEFAZ)
        $consultaUrl = "https://www.fazenda.sp.gov.br/nfce/qrcode";
        
        // Parâmetros do QR Code
        $params = [
            'p' => $sale->nfce_key . '|' . // Chave de acesso
                   '2|' . // Versão do QR Code
                   config('fiscal.environment', '2') . '|' . // Ambiente (1=Prod, 2=Homol)
                   substr(md5($sale->nfce_key . $sale->total), 0, 8) // Digest
        ];
        
        return $consultaUrl . '?' . http_build_query($params);
    }
    
    /**
     * Formata o número da NFCe para exibição
     */
    private function formatNfceNumber(?string $number): string
    {
        if (!$number) return '000000000';
        return str_pad($number, 9, '0', STR_PAD_LEFT);
    }
    
    /**
     * Formata a chave de acesso para exibição
     */
    private function formatAccessKey(?string $key): string
    {
        if (!$key) return 'Chave não disponível';
        
        // Remove espaços e formata em grupos de 4 dígitos
        $cleanKey = preg_replace('/\s+/', '', $key);
        return trim(chunk_split($cleanKey, 4, ' '));
    }
    
    /**
     * Retorna o label da forma de pagamento
     */
    private function getPaymentMethodLabel(?string $method): string
    {
        $methods = [
            'cash' => 'Dinheiro',
            'card' => 'Cartão',
            'debit' => 'Cartão Débito',
            'credit' => 'Cartão Crédito',
            'pix' => 'PIX',
            'transfer' => 'Transferência',
            'check' => 'Cheque'
        ];
        
        return $methods[$method] ?? 'Não informado';
    }
}