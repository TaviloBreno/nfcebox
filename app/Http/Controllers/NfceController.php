<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\Fiscal\DanfeService;
use App\Services\Fiscal\SefazClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class NfceController extends Controller
{
    protected $danfeService;
    protected $sefazClient;

    public function __construct(DanfeService $danfeService, SefazClient $sefazClient)
    {
        $this->danfeService = $danfeService;
        $this->sefazClient = $sefazClient;
    }

    /**
     * Lista paginada de NFC-e
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'items'])
            ->whereNotNull('nfce_key')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('access_key')) {
            $query->where('nfce_key', 'like', '%' . $request->access_key . '%');
        }

        if ($request->filled('sale_number')) {
            $query->where('number', $request->sale_number);
        }

        $sales = $query->paginate(20);
        $customers = \App\Models\Customer::orderBy('name')->get();

        return view('nfce.index', compact('sales', 'customers'));
    }

    /**
     * Visualiza o XML autorizado
     */
    public function viewXml(Sale $sale)
    {
        try {
            if ($sale->status !== 'authorized' || !$sale->xml_path) {
                return response()->json([
                    'error' => 'XML não disponível para esta venda.'
                ], 400);
            }

            if (!Storage::exists($sale->xml_path)) {
                return response()->json([
                    'error' => 'Arquivo XML não encontrado.'
                ], 404);
            }

            $xmlContent = Storage::get($sale->xml_path);
            
            return response($xmlContent, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'inline; filename="nfce_' . $sale->id . '.xml"'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro ao visualizar XML: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Baixa o PDF do DANFE
     */
    public function downloadPdf(Sale $sale)
    {
        try {
            if ($sale->status !== 'authorized') {
                return response()->json([
                    'error' => 'Venda não autorizada. Não é possível gerar DANFE.'
                ], 400);
            }

            $pdfContent = $this->danfeService->generateDanfe($sale);
            $filename = "danfe_nfce_{$sale->number}_{$sale->id}.pdf";
            
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
     * Reimprimir DANFE (visualizar no navegador)
     */
    public function reprint(Sale $sale)
    {
        try {
            if ($sale->status !== 'authorized') {
                return response()->json([
                    'error' => 'Venda não autorizada. Não é possível gerar DANFE.'
                ], 400);
            }

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
     * Verifica se a NFC-e pode ser cancelada
     */
    public function canCancel(Sale $sale)
    {
        if ($sale->status !== 'authorized') {
            return false;
        }

        // Verifica se está dentro do prazo de 30 minutos
        $authorizedAt = Carbon::parse($sale->authorized_at);
        $now = Carbon::now();
        $diffInMinutes = $authorizedAt->diffInMinutes($now);

        return $diffInMinutes <= 30;
    }

    /**
     * Cancela a NFC-e
     */
    public function cancel(Request $request, Sale $sale)
    {
        try {
            // Validações
            if (!$this->canCancel($sale)) {
                return response()->json([
                    'error' => 'NFC-e não pode ser cancelada. Verifique o status e o prazo de cancelamento.'
                ], 400);
            }

            $request->validate([
                'reason' => 'required|string|min:15|max:255'
            ]);

            // Envia evento de cancelamento para SEFAZ
            $cancelResult = $this->sefazClient->cancelNfce($sale, $request->reason);

            if ($cancelResult['success']) {
                // Atualiza a venda
                $sale->update([
                    'status' => 'canceled',
                    'cancellation_reason' => $request->reason,
                    'canceled_at' => now()
                ]);

                // Salva XML do evento de cancelamento
                $this->saveCancellationXml($sale, $cancelResult['xml']);

                return response()->json([
                    'success' => true,
                    'message' => 'NFC-e cancelada com sucesso.'
                ]);
            } else {
                return response()->json([
                    'error' => 'Erro ao cancelar NFC-e: ' . $cancelResult['message']
                ], 400);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro ao cancelar NFC-e: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salva o XML do evento de cancelamento
     */
    private function saveCancellationXml(Sale $sale, string $xmlContent)
    {
        $filename = $sale->nfce_key . '-canc.xml';
        $path = 'nfce/events/' . $filename;
        
        Storage::put($path, $xmlContent);
        
        // Atualiza o caminho do XML de cancelamento na venda
        $sale->update([
            'cancellation_xml_path' => $path
        ]);
    }

    /**
     * Envia para impressora de rede (opcional)
     */
    public function printToNetwork(Request $request, Sale $sale)
    {
        try {
            $request->validate([
                'printer_ip' => 'required|ip',
                'printer_port' => 'integer|min:1|max:65535'
            ]);

            if ($sale->status !== 'authorized') {
                return response()->json([
                    'error' => 'Venda não autorizada.'
                ], 400);
            }

            // Gera o PDF
            $pdfContent = $this->danfeService->generateDanfe($sale);
            
            // Aqui você implementaria a lógica para enviar via RAW para a impressora
            // Por exemplo, usando sockets ou uma biblioteca específica
            $result = $this->sendToNetworkPrinter(
                $request->printer_ip, 
                $request->printer_port ?? 9100, 
                $pdfContent
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'DANFE enviado para impressora com sucesso.'
                ]);
            } else {
                return response()->json([
                    'error' => 'Falha ao enviar para impressora de rede.'
                ], 500);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro ao imprimir: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envia dados para impressora de rede via RAW
     */
    private function sendToNetworkPrinter(string $ip, int $port, string $data): bool
    {
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            
            if (!$socket) {
                return false;
            }

            $result = socket_connect($socket, $ip, $port);
            
            if (!$result) {
                socket_close($socket);
                return false;
            }

            socket_write($socket, $data, strlen($data));
            socket_close($socket);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
}