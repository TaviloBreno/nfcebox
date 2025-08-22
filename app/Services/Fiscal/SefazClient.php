<?php

namespace App\Services\Fiscal;

use App\Models\CompanyConfig;
use App\Models\Sale;
use Exception;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

class SefazClient
{
    private CompanyConfig $companyConfig;
    private string $environment;
    private array $webserviceUrls;
    private int $timeout;
    
    public function __construct(CompanyConfig $companyConfig)
    {
        $this->companyConfig = $companyConfig;
        $this->environment = $companyConfig->environment ?? 'homologacao';
        $this->timeout = 30; // 30 segundos
        
        $this->setWebserviceUrls();
    }
    
    /**
     * Define as URLs dos webservices conforme o ambiente
     */
    private function setWebserviceUrls(): void
    {
        if ($this->environment === 'producao') {
            $this->webserviceUrls = [
                'SP' => [
                    'NfeAutorizacao4' => 'https://nfe.fazenda.sp.gov.br/ws/nfeautorizacao4.asmx',
                    'NfeRetAutorizacao4' => 'https://nfe.fazenda.sp.gov.br/ws/nferetautorizacao4.asmx',
                    'NfeStatusServico4' => 'https://nfe.fazenda.sp.gov.br/ws/nfestatusservico4.asmx'
                ]
            ];
        } else {
            // Homologação
            $this->webserviceUrls = [
                'SP' => [
                    'NfeAutorizacao4' => 'https://homologacao.nfe.fazenda.sp.gov.br/ws/nfeautorizacao4.asmx',
                    'NfeRetAutorizacao4' => 'https://homologacao.nfe.fazenda.sp.gov.br/ws/nferetautorizacao4.asmx',
                    'NfeStatusServico4' => 'https://homologacao.nfe.fazenda.sp.gov.br/ws/nfestatusservico4.asmx'
                ]
            ];
        }
    }
    
    /**
     * Envia o XML da NFCe para autorização
     */
    public function sendNfce(string $signedXml, Sale $sale): array
    {
        try {
            Log::info('Iniciando envio NFCe para SEFAZ', [
                'sale_id' => $sale->id,
                'environment' => $this->environment
            ]);
            
            // Monta o envelope SOAP para autorização
            $soapEnvelope = $this->buildAuthorizationSoapEnvelope($signedXml);
            
            // Envia para autorização
            $response = $this->sendSoapRequest('NfeAutorizacao4', $soapEnvelope);
            
            // Processa resposta
            $result = $this->processAuthorizationResponse($response, $sale);
            
            if ($result['success']) {
                Log::info('NFCe enviada com sucesso', [
                    'sale_id' => $sale->id,
                    'receipt' => $result['receipt'] ?? null
                ]);
            }
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Erro ao enviar NFCe para SEFAZ', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }
    
    /**
     * Consulta o resultado do processamento
     */
    public function queryProcessingResult(string $receipt, Sale $sale): array
    {
        try {
            Log::info('Consultando resultado do processamento', [
                'sale_id' => $sale->id,
                'receipt' => $receipt
            ]);
            
            // Monta envelope SOAP para consulta
            $soapEnvelope = $this->buildQuerySoapEnvelope($receipt);
            
            // Envia consulta
            $response = $this->sendSoapRequest('NfeRetAutorizacao4', $soapEnvelope);
            
            // Processa resposta
            $result = $this->processQueryResponse($response, $sale);
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Erro ao consultar resultado do processamento', [
                'sale_id' => $sale->id,
                'receipt' => $receipt,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }
    
    /**
     * Verifica o status do serviço da SEFAZ
     */
    public function checkServiceStatus(): array
    {
        try {
            $soapEnvelope = $this->buildStatusSoapEnvelope();
            $response = $this->sendSoapRequest('NfeStatusServico4', $soapEnvelope);
            
            return $this->processStatusResponse($response);
            
        } catch (Exception $e) {
            Log::error('Erro ao verificar status do serviço SEFAZ', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'offline'
            ];
        }
    }
    
    /**
     * Envia requisição SOAP
     */
    private function sendSoapRequest(string $service, string $soapEnvelope): string
    {
        $address = $this->companyConfig->address_json;
        if (is_string($address)) {
            $address = json_decode($address, true);
        }
        $state = $address['state'] ?? 'SP';
        
        $url = $this->webserviceUrls[$state][$service] ?? $this->webserviceUrls['SP'][$service];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: "http://www.portalfiscal.inf.br/nfe/wsdl/NFeAutorizacao4/' . $service . '"'
                ],
                'content' => $soapEnvelope,
                'timeout' => $this->timeout
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Falha na comunicação com o webservice da SEFAZ');
        }
        
        return $response;
    }
    
    /**
     * Monta envelope SOAP para autorização
     */
    private function buildAuthorizationSoapEnvelope(string $signedXml): string
    {
        $address = $this->companyConfig->address_json;
        if (is_string($address)) {
            $address = json_decode($address, true);
        }
        $state = $address['state'] ?? 'SP';
        $cUF = $this->getUfCode($state);
        
        return '<?xml version="1.0" encoding="utf-8"?>' .
               '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" ' .
               'xmlns:nfe="http://www.portalfiscal.inf.br/nfe/wsdl/NFeAutorizacao4">' .
               '<soap:Header />' .
               '<soap:Body>' .
               '<nfe:nfeAutorizacaoLote>' .
               '<nfe:nfeDadosMsg>' .
               '<enviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">' .
               '<idLote>1</idLote>' .
               '<indSinc>1</indSinc>' .
               $signedXml .
               '</enviNFe>' .
               '</nfe:nfeDadosMsg>' .
               '</nfe:nfeAutorizacaoLote>' .
               '</soap:Body>' .
               '</soap:Envelope>';
    }
    
    /**
     * Monta envelope SOAP para consulta
     */
    private function buildQuerySoapEnvelope(string $receipt): string
    {
        $address = $this->companyConfig->address_json;
        if (is_string($address)) {
            $address = json_decode($address, true);
        }
        $state = $address['state'] ?? 'SP';
        $cUF = $this->getUfCode($state);
        
        return '<?xml version="1.0" encoding="utf-8"?>' .
               '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" ' .
               'xmlns:nfe="http://www.portalfiscal.inf.br/nfe/wsdl/NFeRetAutorizacao4">' .
               '<soap:Header />' .
               '<soap:Body>' .
               '<nfe:nfeRetAutorizacaoLote>' .
               '<nfe:nfeDadosMsg>' .
               '<consReciNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">' .
               '<tpAmb>' . ($this->environment === 'producao' ? '1' : '2') . '</tpAmb>' .
               '<nRec>' . $receipt . '</nRec>' .
               '</consReciNFe>' .
               '</nfe:nfeDadosMsg>' .
               '</nfe:nfeRetAutorizacaoLote>' .
               '</soap:Body>' .
               '</soap:Envelope>';
    }
    
    /**
     * Monta envelope SOAP para status do serviço
     */
    private function buildStatusSoapEnvelope(): string
    {
        $address = $this->companyConfig->address_json;
        if (is_string($address)) {
            $address = json_decode($address, true);
        }
        $state = $address['state'] ?? 'SP';
        $cUF = $this->getUfCode($state);
        
        return '<?xml version="1.0" encoding="utf-8"?>' .
               '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" ' .
               'xmlns:nfe="http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4">' .
               '<soap:Header />' .
               '<soap:Body>' .
               '<nfe:nfeStatusServicoNF>' .
               '<nfe:nfeDadosMsg>' .
               '<consStatServ xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">' .
               '<tpAmb>' . ($this->environment === 'producao' ? '1' : '2') . '</tpAmb>' .
               '<cUF>' . $cUF . '</cUF>' .
               '<xServ>STATUS</xServ>' .
               '</consStatServ>' .
               '</nfe:nfeDadosMsg>' .
               '</nfe:nfeStatusServicoNF>' .
               '</soap:Body>' .
               '</soap:Envelope>';
    }
    
    /**
     * Processa resposta de autorização
     */
    private function processAuthorizationResponse(string $response, Sale $sale): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($response);
        
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica se há erro SOAP
        $faultNodes = $xpath->query('//soap:Fault');
        if ($faultNodes->length > 0) {
            $faultString = $xpath->query('//soap:Fault/soap:Reason/soap:Text')->item(0)->nodeValue ?? 'Erro SOAP desconhecido';
            throw new Exception('Erro SOAP: ' . $faultString);
        }
        
        // Processa resposta de envio
        $cStatNodes = $xpath->query('//nfe:cStat');
        $xMotivoNodes = $xpath->query('//nfe:xMotivo');
        $nRecNodes = $xpath->query('//nfe:nRec');
        
        if ($cStatNodes->length === 0) {
            throw new Exception('Resposta inválida da SEFAZ - código de status não encontrado');
        }
        
        $cStat = $cStatNodes->item(0)->nodeValue;
        $xMotivo = $xMotivoNodes->item(0)->nodeValue ?? 'Motivo não informado';
        $nRec = $nRecNodes->length > 0 ? $nRecNodes->item(0)->nodeValue : null;
        
        // Status 103 = Lote recebido com sucesso
        if ($cStat === '103') {
            return [
                'success' => true,
                'status' => 'processing',
                'receipt' => $nRec,
                'message' => $xMotivo
            ];
        }
        
        // Outros status são erros
        return [
            'success' => false,
            'status' => 'rejected',
            'code' => $cStat,
            'message' => $xMotivo
        ];
    }
    
    /**
     * Processa resposta de consulta
     */
    private function processQueryResponse(string $response, Sale $sale): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($response);
        
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica status do lote
        $cStatNodes = $xpath->query('//nfe:cStat');
        if ($cStatNodes->length === 0) {
            throw new Exception('Resposta inválida da SEFAZ - status não encontrado');
        }
        
        $cStat = $cStatNodes->item(0)->nodeValue;
        
        // Status 104 = Lote processado
        if ($cStat === '104') {
            // Verifica status da NFCe individual
            $protNFeNodes = $xpath->query('//nfe:protNFe');
            
            if ($protNFeNodes->length > 0) {
                $infProtNodes = $xpath->query('//nfe:infProt/nfe:cStat');
                $nProtNodes = $xpath->query('//nfe:infProt/nfe:nProt');
                $chNFeNodes = $xpath->query('//nfe:infProt/nfe:chNFe');
                $xMotivoNodes = $xpath->query('//nfe:infProt/nfe:xMotivo');
                
                if ($infProtNodes->length > 0) {
                    $nfceStat = $infProtNodes->item(0)->nodeValue;
                    $protocol = $nProtNodes->length > 0 ? $nProtNodes->item(0)->nodeValue : null;
                    $accessKey = $chNFeNodes->length > 0 ? $chNFeNodes->item(0)->nodeValue : null;
                    $message = $xMotivoNodes->length > 0 ? $xMotivoNodes->item(0)->nodeValue : 'Autorizada';
                    
                    // Status 100 = Autorizada
                    if ($nfceStat === '100') {
                        $this->updateSaleAsAuthorized($sale, $accessKey, $protocol);
                        
                        return [
                            'success' => true,
                            'status' => 'authorized',
                            'protocol' => $protocol,
                            'access_key' => $accessKey,
                            'message' => $message
                        ];
                    } else {
                        // NFCe rejeitada
                        $this->logSaleError($sale, $nfceStat, $message);
                        
                        return [
                            'success' => false,
                            'status' => 'rejected',
                            'code' => $nfceStat,
                            'message' => $message
                        ];
                    }
                }
            }
        }
        
        // Lote ainda em processamento
        if ($cStat === '105') {
            return [
                'success' => false,
                'status' => 'processing',
                'message' => 'Lote ainda em processamento'
            ];
        }
        
        // Outros erros
        $xMotivoNodes = $xpath->query('//nfe:xMotivo');
        $message = $xMotivoNodes->length > 0 ? $xMotivoNodes->item(0)->nodeValue : 'Erro desconhecido';
        
        return [
            'success' => false,
            'status' => 'error',
            'code' => $cStat,
            'message' => $message
        ];
    }
    
    /**
     * Processa resposta de status do serviço
     */
    private function processStatusResponse(string $response): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($response);
        
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        $cStatNodes = $xpath->query('//nfe:cStat');
        $xMotivoNodes = $xpath->query('//nfe:xMotivo');
        
        if ($cStatNodes->length === 0) {
            throw new Exception('Resposta inválida do status do serviço');
        }
        
        $cStat = $cStatNodes->item(0)->nodeValue;
        $xMotivo = $xMotivoNodes->item(0)->nodeValue ?? 'Status não informado';
        
        // Status 107 = Serviço em operação
        $isOnline = $cStat === '107';
        
        return [
            'success' => $isOnline,
            'status' => $isOnline ? 'online' : 'offline',
            'code' => $cStat,
            'message' => $xMotivo
        ];
    }
    
    /**
     * Atualiza a venda como autorizada
     */
    private function updateSaleAsAuthorized(Sale $sale, ?string $accessKey, ?string $protocol): void
    {
        $sale->update([
            'status' => 'authorized',
            'nfce_key' => $accessKey,
            'protocol' => $protocol,
            'authorized_at' => now()
        ]);
        
        Log::info('Venda autorizada com sucesso', [
            'sale_id' => $sale->id,
            'access_key' => $accessKey,
            'protocol' => $protocol
        ]);
    }
    
    /**
     * Registra erro da venda
     */
    private function logSaleError(Sale $sale, string $code, string $message): void
    {
        Log::error('NFCe rejeitada pela SEFAZ', [
            'sale_id' => $sale->id,
            'code' => $code,
            'message' => $message
        ]);
        
        // Mantém status como authorized_pending para nova tentativa
        $sale->update([
            'status' => 'authorized_pending',
            'error_message' => "Código {$code}: {$message}"
        ]);
    }
    
    /**
     * Obtém o código da UF
     */
    private function getUfCode(string $uf): string
    {
        $codes = [
            'AC' => '12', 'AL' => '17', 'AP' => '16', 'AM' => '23', 'BA' => '29',
            'CE' => '23', 'DF' => '53', 'ES' => '32', 'GO' => '52', 'MA' => '21',
            'MT' => '51', 'MS' => '50', 'MG' => '31', 'PA' => '15', 'PB' => '25',
            'PR' => '41', 'PE' => '26', 'PI' => '22', 'RJ' => '33', 'RN' => '24',
            'RS' => '43', 'RO' => '11', 'RR' => '14', 'SC' => '42', 'SP' => '35',
            'SE' => '28', 'TO' => '17'
        ];
        
        return $codes[$uf] ?? '35'; // Default SP
    }
    
    /**
     * Define timeout personalizado
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }
    
    /**
     * Retorna o ambiente configurado
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
}