<?php

namespace App\Jobs;

use App\Models\Sale;
use App\Services\Fiscal\SefazClient;
use App\Services\Fiscal\NfceBuilderService;
use App\Services\Fiscal\XmlSignerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class TransmitNfceJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [30, 60, 120, 300, 600]; // 30s, 1m, 2m, 5m, 10m

    /**
     * The Sale instance.
     *
     * @var Sale
     */
    protected $sale;

    /**
     * Create a new job instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
        $this->onQueue('nfce-transmission');
    }

    /**
     * Execute the job.
     */
    public function handle(
        SefazClient $sefazClient,
        NfceBuilderService $nfceBuilder,
        XmlSignerService $xmlSigner
    ): void {
        try {
            Log::info('Iniciando transmissão de NFCe via Job', [
                'sale_id' => $this->sale->id,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries
            ]);

            // Verifica se a venda ainda precisa ser transmitida
            if (!$this->shouldTransmit()) {
                Log::info('NFCe não precisa ser transmitida', [
                    'sale_id' => $this->sale->id,
                    'status' => $this->sale->status
                ]);
                return;
            }

            // Atualiza status para processando
            $this->sale->update([
                'status' => 'processing',
                'error_message' => null
            ]);

            // Gera o XML da NFCe
            $xmlContent = $nfceBuilder->buildNfceXml($this->sale);
            
            // Assina o XML
            $signedXml = $xmlSigner->signXml($xmlContent);
            
            // Envia para SEFAZ
            $result = $sefazClient->sendNfce($signedXml, $this->sale);
            
            if ($result['success']) {
                $this->handleSuccess($result);
            } else {
                $this->handleError($result);
            }
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Verifica se a NFCe deve ser transmitida.
     */
    private function shouldTransmit(): bool
    {
        // Recarrega a venda do banco para ter dados atualizados
        $this->sale->refresh();
        
        // Só transmite se estiver em draft, authorized_pending, error ou processing
        return in_array($this->sale->status, ['draft', 'authorized_pending', 'error', 'processing']);
    }

    /**
     * Trata sucesso na transmissão.
     */
    private function handleSuccess(array $result): void
    {
        Log::info('NFCe transmitida com sucesso via Job', [
            'sale_id' => $this->sale->id,
            'status' => $result['status'] ?? 'unknown',
            'protocol' => $result['protocol'] ?? null
        ]);

        // Atualiza a venda com os dados de sucesso
        $updateData = [
            'status' => $result['status'] ?? 'authorized',
            'error_message' => null
        ];

        if (isset($result['protocol'])) {
            $updateData['protocol'] = $result['protocol'];
        }

        if (isset($result['access_key'])) {
            $updateData['nfce_key'] = $result['access_key'];
        }

        if ($result['status'] === 'authorized') {
            $updateData['authorized_at'] = now();
        }

        $this->sale->update($updateData);
    }

    /**
     * Trata erro na transmissão.
     */
    private function handleError(array $result): void
    {
        $errorMessage = $result['error'] ?? $result['message'] ?? 'Erro desconhecido';
        
        Log::warning('Erro na transmissão de NFCe via Job', [
            'sale_id' => $this->sale->id,
            'attempt' => $this->attempts(),
            'error' => $errorMessage,
            'will_retry' => $this->attempts() < $this->tries
        ]);

        // Se ainda há tentativas, não atualiza como erro final
        if ($this->attempts() < $this->tries) {
            // Lança exceção para triggerar retry
            throw new Exception($errorMessage);
        }

        // Última tentativa falhou, marca como erro
        $this->sale->update([
            'status' => 'error',
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Trata exceções não capturadas.
     */
    private function handleException(Exception $e): void
    {
        Log::error('Exceção na transmissão de NFCe via Job', [
            'sale_id' => $this->sale->id,
            'attempt' => $this->attempts(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'will_retry' => $this->attempts() < $this->tries
        ]);

        // Se ainda há tentativas, relança a exceção para retry
        if ($this->attempts() < $this->tries) {
            throw $e;
        }

        // Última tentativa, marca como erro
        $this->sale->update([
            'status' => 'error',
            'error_message' => 'Erro interno: ' . $e->getMessage()
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Job de transmissão de NFCe falhou definitivamente', [
            'sale_id' => $this->sale->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Marca a venda como erro se ainda não foi marcada
        if ($this->sale->status !== 'error') {
            $this->sale->update([
                'status' => 'error',
                'error_message' => 'Falha na transmissão após ' . $this->tries . ' tentativas: ' . $exception->getMessage()
            ]);
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return $this->backoff;
    }
}
