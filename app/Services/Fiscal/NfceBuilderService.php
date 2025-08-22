<?php

namespace App\Services\Fiscal;

use App\Models\CompanyConfig;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Fiscal\DigitalSignatureService;
use DOMDocument;
use DOMElement;
use Exception;
use Illuminate\Support\Facades\Log;

class NfceBuilderService
{
    private CompanyConfig $companyConfig;
    private DOMDocument $dom;
    private string $accessKey;
    private array $totals;
    private DigitalSignatureService $signatureService;

    public function __construct(CompanyConfig $companyConfig)
    {
        $this->companyConfig = $companyConfig;
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
        $this->totals = [];
        $this->signatureService = new DigitalSignatureService($companyConfig);
    }

    /**
     * Constrói o XML da NFCe completo
     */
    public function buildNfceXml(Sale $sale, Customer $customer = null): string
    {
        try {
            // Gera a chave de acesso
            $this->generateAccessKey($sale);
            
            // Calcula os totais
            $this->calculateTotals($sale);
            
            // Monta a estrutura do XML
            $this->buildXmlStructure($sale, $customer);
            
            // Assina digitalmente
            $this->signXml();
            
            return $this->dom->saveXML();
        } catch (Exception $e) {
            Log::error('Erro ao construir XML da NFCe: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gera a chave de acesso da NFCe
     */
    private function generateAccessKey(Sale $sale): void
    {
        // Código UF (2 dígitos)
        $address = $this->companyConfig->address_json;
        if (is_string($address)) {
            $address = json_decode($address, true);
        }
        $state = $address['state'] ?? null;
        $cUF = $this->getUfCode($state);
        
        // AAMM (Ano e Mês de emissão)
        $AAMM = date('ym', strtotime($sale->created_at));
        
        // CNPJ do emitente (14 dígitos)
        $cnpj = preg_replace('/\D/', '', $this->companyConfig->cnpj);
        
        // Modelo (55 para NFCe)
        $mod = '65';
        
        // Série (3 dígitos)
        $serie = str_pad('1', 3, '0', STR_PAD_LEFT);
        
        // Número da NFCe (9 dígitos)
        $nNF = str_pad($sale->sale_number, 9, '0', STR_PAD_LEFT);
        
        // Tipo de emissão (1 = Normal)
        $tpEmis = '1';
        
        // Código numérico (8 dígitos aleatórios)
        $cNF = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        
        // Monta a chave sem o DV
        $chaveBase = $cUF . $AAMM . $cnpj . $mod . $serie . $nNF . $tpEmis . $cNF;
        
        // Calcula o dígito verificador
        $dv = $this->calculateAccessKeyDV($chaveBase);
        
        $this->accessKey = $chaveBase . $dv;
    }

    /**
     * Calcula o dígito verificador da chave de acesso
     */
    private function calculateAccessKeyDV(string $chave): string
    {
        $sequencia = '4329876543298765432987654329876543298765432';
        $soma = 0;
        
        for ($i = 0; $i < 43; $i++) {
            $soma += intval($chave[$i]) * intval($sequencia[$i]);
        }
        
        $resto = $soma % 11;
        $dv = $resto < 2 ? 0 : 11 - $resto;
        
        return (string) $dv;
    }

    /**
     * Calcula os totais da venda
     */
    private function calculateTotals(Sale $sale): void
    {
        $vBC = 0;
        $vICMS = 0;
        $vBCST = 0;
        $vST = 0;
        $vProd = 0;
        $vFrete = 0;
        $vSeg = 0;
        $vDesc = $sale->discount ?? 0;
        $vII = 0;
        $vIPI = 0;
        $vPIS = 0;
        $vCOFINS = 0;
        $vOutro = 0;
        
        foreach ($sale->saleItems as $item) {
            $vProd += $item->qty * $item->unit_price;
        }
        
        $vNF = $vProd - $vDesc + $vOutro + $vST + $vFrete + $vSeg + $vII + $vIPI;
        
        $this->totals = [
            'vBC' => number_format($vBC, 2, '.', ''),
            'vICMS' => number_format($vICMS, 2, '.', ''),
            'vBCST' => number_format($vBCST, 2, '.', ''),
            'vST' => number_format($vST, 2, '.', ''),
            'vProd' => number_format($vProd, 2, '.', ''),
            'vFrete' => number_format($vFrete, 2, '.', ''),
            'vSeg' => number_format($vSeg, 2, '.', ''),
            'vDesc' => number_format($vDesc, 2, '.', ''),
            'vII' => number_format($vII, 2, '.', ''),
            'vIPI' => number_format($vIPI, 2, '.', ''),
            'vPIS' => number_format($vPIS, 2, '.', ''),
            'vCOFINS' => number_format($vCOFINS, 2, '.', ''),
            'vOutro' => number_format($vOutro, 2, '.', ''),
            'vNF' => number_format($vNF, 2, '.', '')
        ];
    }

    /**
     * Constrói a estrutura XML da NFCe
     */
    private function buildXmlStructure(Sale $sale, Customer $customer = null): void
    {
        // Elemento raiz
        $nfeProc = $this->dom->createElement('nfeProc');
        $nfeProc->setAttribute('versao', '4.00');
        $nfeProc->setAttribute('xmlns', 'http://www.portalfiscal.inf.br/nfe');
        $this->dom->appendChild($nfeProc);
        
        // NFe
        $nfe = $this->dom->createElement('NFe');
        $nfe->setAttribute('xmlns', 'http://www.portalfiscal.inf.br/nfe');
        $nfeProc->appendChild($nfe);
        
        // infNFe
        $infNFe = $this->dom->createElement('infNFe');
        $infNFe->setAttribute('Id', 'NFe' . $this->accessKey);
        $nfe->appendChild($infNFe);
        
        // Identificação
        $this->buildIde($infNFe, $sale);
        
        // Emitente
        $this->buildEmit($infNFe);
        
        // Destinatário (se houver)
        if ($customer) {
            $this->buildDest($infNFe, $customer);
        }
        
        // Produtos/Serviços
        $this->buildDet($infNFe, $sale);
        
        // Totais
        $this->buildTotal($infNFe);
        
        // Transporte
        $this->buildTransp($infNFe);
        
        // Pagamento
        $this->buildPag($infNFe, $sale);
        
        // Informações adicionais
        $this->buildInfAdic($infNFe);
    }

    /**
     * Constrói o grupo de identificação
     */
    private function buildIde(DOMElement $infNFe, Sale $sale): void
    {
        $ide = $this->dom->createElement('ide');
        $infNFe->appendChild($ide);
        
        $address = $this->companyConfig->address_json;
        if (is_string($address)) {
            $address = json_decode($address, true);
        }
        $state = $address['state'] ?? null;
        $ide->appendChild($this->dom->createElement('cUF', $this->getUfCode($state)));
        $ide->appendChild($this->dom->createElement('cNF', substr($this->accessKey, 35, 8)));
        $ide->appendChild($this->dom->createElement('natOp', 'Venda'));
        $ide->appendChild($this->dom->createElement('mod', '65'));
        $ide->appendChild($this->dom->createElement('serie', '1'));
        $ide->appendChild($this->dom->createElement('nNF', $sale->sale_number));
        $ide->appendChild($this->dom->createElement('dhEmi', date('c', strtotime($sale->created_at))));
        $ide->appendChild($this->dom->createElement('tpNF', '1'));
        $ide->appendChild($this->dom->createElement('idDest', '1'));
        $ide->appendChild($this->dom->createElement('cMunFG', $this->companyConfig->city_code ?? '3550308'));
        $ide->appendChild($this->dom->createElement('tpImp', '4'));
        $ide->appendChild($this->dom->createElement('tpEmis', '1'));
        $ide->appendChild($this->dom->createElement('cDV', substr($this->accessKey, -1)));
        $ide->appendChild($this->dom->createElement('tpAmb', '2')); // 2 = Homologação
        $ide->appendChild($this->dom->createElement('finNFe', '1'));
        $ide->appendChild($this->dom->createElement('indFinal', '1'));
        $ide->appendChild($this->dom->createElement('indPres', '1'));
        $ide->appendChild($this->dom->createElement('procEmi', '0'));
        $ide->appendChild($this->dom->createElement('verProc', '1.0'));
    }

    /**
     * Constrói os dados do emitente
     */
    private function buildEmit(DOMElement $infNFe): void
    {
        $emit = $this->dom->createElement('emit');
        $infNFe->appendChild($emit);
        
        $emit->appendChild($this->dom->createElement('CNPJ', preg_replace('/\D/', '', $this->companyConfig->cnpj)));
        $emit->appendChild($this->dom->createElement('xNome', $this->companyConfig->corporate_name));
        $emit->appendChild($this->dom->createElement('xFant', $this->companyConfig->trade_name ?? $this->companyConfig->corporate_name));
        
        // Endereço
        $enderEmit = $this->dom->createElement('enderEmit');
        $emit->appendChild($enderEmit);
        
        $address = $this->companyConfig->address_json;
        if (is_string($address)) {
            $address = json_decode($address, true);
        }
        
        $enderEmit->appendChild($this->dom->createElement('xLgr', $address['street'] ?? ''));
        $enderEmit->appendChild($this->dom->createElement('nro', $address['number'] ?? ''));
        if (!empty($address['complement'])) {
            $enderEmit->appendChild($this->dom->createElement('xCpl', $address['complement']));
        }
        $enderEmit->appendChild($this->dom->createElement('xBairro', $address['neighborhood'] ?? ''));
        $enderEmit->appendChild($this->dom->createElement('cMun', $address['city_code'] ?? '3550308'));
        $enderEmit->appendChild($this->dom->createElement('xMun', $address['city'] ?? ''));
        $enderEmit->appendChild($this->dom->createElement('UF', $address['state'] ?? ''));
        $enderEmit->appendChild($this->dom->createElement('CEP', preg_replace('/\D/', '', $address['zip_code'] ?? '')));
        $enderEmit->appendChild($this->dom->createElement('cPais', '1058'));
        $enderEmit->appendChild($this->dom->createElement('xPais', 'Brasil'));
        if (!empty($address['phone'])) {
            $enderEmit->appendChild($this->dom->createElement('fone', preg_replace('/\D/', '', $address['phone'])));
        }
        
        $emit->appendChild($this->dom->createElement('IE', preg_replace('/\D/', '', $this->companyConfig->ie)));
        $emit->appendChild($this->dom->createElement('CRT', $this->companyConfig->tax_regime ?? '1'));
    }

    /**
     * Constrói os dados do destinatário
     */
    private function buildDest(DOMElement $infNFe, Customer $customer): void
    {
        $dest = $this->dom->createElement('dest');
        $infNFe->appendChild($dest);
        
        if ($customer->document) {
            $document = preg_replace('/\D/', '', $customer->document);
            if (strlen($document) == 11) {
                $dest->appendChild($this->dom->createElement('CPF', $document));
            } else {
                $dest->appendChild($this->dom->createElement('CNPJ', $document));
            }
        }
        
        $dest->appendChild($this->dom->createElement('xNome', $customer->name));
        
        if ($customer->email) {
            $dest->appendChild($this->dom->createElement('email', $customer->email));
        }
    }

    /**
     * Constrói os detalhes dos produtos
     */
    private function buildDet(DOMElement $infNFe, Sale $sale): void
    {
        foreach ($sale->saleItems as $index => $item) {
            $det = $this->dom->createElement('det');
            $det->setAttribute('nItem', $index + 1);
            $infNFe->appendChild($det);
            
            // Produto
            $prod = $this->dom->createElement('prod');
            $det->appendChild($prod);
            
            $prod->appendChild($this->dom->createElement('cProd', $item->product->id));
            $prod->appendChild($this->dom->createElement('cEAN', ''));
            $prod->appendChild($this->dom->createElement('xProd', $item->product->name));
            $prod->appendChild($this->dom->createElement('NCM', $item->product->ncm ?? '00000000'));
            if ($item->product->cest) {
                $prod->appendChild($this->dom->createElement('CEST', $item->product->cest));
            }
            $prod->appendChild($this->dom->createElement('CFOP', $item->product->cfop ?? '5102'));
            $prod->appendChild($this->dom->createElement('uCom', $item->product->unit ?? 'UN'));
            $prod->appendChild($this->dom->createElement('qCom', number_format($item->qty, 4, '.', '')));
            $prod->appendChild($this->dom->createElement('vUnCom', number_format($item->unit_price, 2, '.', '')));
            $prod->appendChild($this->dom->createElement('vProd', number_format($item->qty * $item->unit_price, 2, '.', '')));
            $prod->appendChild($this->dom->createElement('cEANTrib', ''));
            $prod->appendChild($this->dom->createElement('uTrib', $item->product->unit));
            $prod->appendChild($this->dom->createElement('qTrib', number_format($item->qty, 4, '.', '')));
            $prod->appendChild($this->dom->createElement('vUnTrib', number_format($item->unit_price, 10, '.', '')));
            $prod->appendChild($this->dom->createElement('indTot', '1'));
            
            // Impostos
            $this->buildImposto($det, $item);
        }
    }

    /**
     * Constrói os impostos do item
     */
    private function buildImposto(DOMElement $det, SaleItem $item): void
    {
        $imposto = $this->dom->createElement('imposto');
        $det->appendChild($imposto);
        
        // ICMS
        $icms = $this->dom->createElement('ICMS');
        $imposto->appendChild($icms);
        
        $icmssn102 = $this->dom->createElement('ICMSSN102');
        $icms->appendChild($icmssn102);
        
        $icmssn102->appendChild($this->dom->createElement('orig', '0'));
        $icmssn102->appendChild($this->dom->createElement('CSOSN', '102'));
        
        // PIS
        $pis = $this->dom->createElement('PIS');
        $imposto->appendChild($pis);
        
        $pisnt = $this->dom->createElement('PISNT');
        $pis->appendChild($pisnt);
        
        $pisnt->appendChild($this->dom->createElement('CST', '07'));
        
        // COFINS
        $cofins = $this->dom->createElement('COFINS');
        $imposto->appendChild($cofins);
        
        $cofinsnt = $this->dom->createElement('COFINSNT');
        $cofins->appendChild($cofinsnt);
        
        $cofinsnt->appendChild($this->dom->createElement('CST', '07'));
    }

    /**
     * Constrói os totais
     */
    private function buildTotal(DOMElement $infNFe): void
    {
        $total = $this->dom->createElement('total');
        $infNFe->appendChild($total);
        
        $icmstot = $this->dom->createElement('ICMSTot');
        $total->appendChild($icmstot);
        
        foreach ($this->totals as $key => $value) {
            $icmstot->appendChild($this->dom->createElement($key, $value));
        }
    }

    /**
     * Constrói informações de transporte
     */
    private function buildTransp(DOMElement $infNFe): void
    {
        $transp = $this->dom->createElement('transp');
        $infNFe->appendChild($transp);
        
        $transp->appendChild($this->dom->createElement('modFrete', '9'));
    }

    /**
     * Constrói informações de pagamento
     */
    private function buildPag(DOMElement $infNFe, Sale $sale): void
    {
        $pag = $this->dom->createElement('pag');
        $infNFe->appendChild($pag);
        
        $detPag = $this->dom->createElement('detPag');
        $pag->appendChild($detPag);
        
        $detPag->appendChild($this->dom->createElement('tPag', '01')); // Dinheiro
        $detPag->appendChild($this->dom->createElement('vPag', number_format($sale->total, 2, '.', '')));
    }

    /**
     * Constrói informações adicionais
     */
    private function buildInfAdic(DOMElement $infNFe): void
    {
        $infAdic = $this->dom->createElement('infAdic');
        $infNFe->appendChild($infAdic);
        
        $infAdic->appendChild($this->dom->createElement('infCpl', 'NFCe emitida em ambiente de homologação'));
    }

    /**
     * Assina digitalmente o XML
     */
    private function signXml(): void
    {
        try {
            $this->dom = $this->signatureService->signXml($this->dom);
        } catch (Exception $e) {
            Log::error('Erro ao assinar XML da NFCe: ' . $e->getMessage());
            throw new Exception('Falha na assinatura digital: ' . $e->getMessage());
        }
    }

    /**
     * Gera a URL do QR Code
     */
    public function generateQrCodeUrl(Sale $sale): string
    {
        $url = 'https://www.fazenda.sp.gov.br/nfce/qrcode';
        $params = [
            'chNFe' => $this->accessKey,
            'nVersao' => '100',
            'tpAmb' => '2', // Homologação
            'cDest' => '',
            'dhEmi' => date('Y-m-d\TH:i:s-03:00', strtotime($sale->created_at)),
            'vNF' => number_format($sale->total, 2, '.', ''),
            'vICMS' => '0.00',
            'digVal' => $this->generateDigVal($sale)
        ];
        
        return $url . '?' . http_build_query($params);
    }

    /**
     * Gera o digest value para o QR Code
     */
    private function generateDigVal(Sale $sale): string
    {
        // Simplificado para homologação
        return substr(sha1($this->accessKey . $sale->total), 0, 28);
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
     * Retorna a chave de acesso gerada
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * Retorna os totais calculados
     */
    public function getTotals(): array
    {
        return $this->totals;
    }
}