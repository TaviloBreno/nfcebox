<?php

namespace Tests\Unit\Services;

use App\Models\CompanyConfig;
use App\Services\Fiscal\SefazClientService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SefazClientServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyConfig $companyConfig;
    private MockHandler $mockHandler;
    private SefazClientService $sefazService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria configuração de empresa de teste
        $this->companyConfig = CompanyConfig::create([
            'corporate_name' => 'EMPRESA TESTE LTDA',
            'trade_name' => 'Loja Teste',
            'cnpj' => '11222333000181',
            'ie' => '123456789',
            'environment' => 'homologacao',
            'nfce_series' => 1,
            'nfce_number' => 1,
            'csc_id' => '000001',
            'csc_token' => 'ABCDEF123456789',
            'address_json' => json_encode([
                'street' => 'Rua Teste',
                'number' => '123',
                'city' => 'São Paulo',
                'city_code' => '3550308',
                'state' => 'SP',
                'zip_code' => '01234567'
            ])
        ]);
        
        // Configura mock handler para requisições HTTP
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        // Inicializa o serviço com cliente HTTP mockado
        $this->sefazService = new SefazClientService($this->companyConfig, $httpClient);
    }

    /**
     * Testa a autorização de NFCe com sucesso
     */
    public function test_authorizes_nfce_successfully(): void
    {
        // Mock da resposta de autorização bem-sucedida
        $successResponse = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
            <soap:Body>
                <nfeAutorizacaoLoteResponse>
                    <nfeAutorizacaoLoteResult>
                        <retEnviNFe versao="4.00">
                            <tpAmb>2</tpAmb>
                            <verAplic>SP_NFE_PL_008i2</verAplic>
                            <cStat>103</cStat>
                            <xMotivo>Lote recebido com sucesso</xMotivo>
                            <cUF>35</cUF>
                            <dhRecbto>2024-01-15T10:30:00-03:00</dhRecbto>
                            <infRec>
                                <nRec>351000000000123</nRec>
                                <tMed>1</tMed>
                            </infRec>
                        </retEnviNFe>
                    </nfeAutorizacaoLoteResult>
                </nfeAutorizacaoLoteResponse>
            </soap:Body>
        </soap:Envelope>';
        
        $this->mockHandler->append(new Response(200, [], $successResponse));
        
        $xmlNfce = $this->createSampleNfceXml();
        $result = $this->sefazService->authorize($xmlNfce);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('103', $result['status_code']);
        $this->assertEquals('Lote recebido com sucesso', $result['message']);
        $this->assertEquals('351000000000123', $result['receipt_number']);
    }

    /**
     * Testa a autorização de NFCe com rejeição
     */
    public function test_handles_nfce_rejection(): void
    {
        // Mock da resposta de rejeição
        $rejectionResponse = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
            <soap:Body>
                <nfeAutorizacaoLoteResponse>
                    <nfeAutorizacaoLoteResult>
                        <retEnviNFe versao="4.00">
                            <tpAmb>2</tpAmb>
                            <verAplic>SP_NFE_PL_008i2</verAplic>
                            <cStat>225</cStat>
                            <xMotivo>Rejeição: Falha no Schema XML</xMotivo>
                            <cUF>35</cUF>
                            <dhRecbto>2024-01-15T10:30:00-03:00</dhRecbto>
                        </retEnviNFe>
                    </nfeAutorizacaoLoteResult>
                </nfeAutorizacaoLoteResponse>
            </soap:Body>
        </soap:Envelope>';
        
        $this->mockHandler->append(new Response(200, [], $rejectionResponse));
        
        $xmlNfce = $this->createSampleNfceXml();
        $result = $this->sefazService->authorize($xmlNfce);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('225', $result['status_code']);
        $this->assertEquals('Rejeição: Falha no Schema XML', $result['message']);
    }

    /**
     * Testa a consulta de recibo com autorização
     */
    public function test_queries_receipt_with_authorization(): void
    {
        // Mock da resposta de consulta de recibo com autorização
        $receiptResponse = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
            <soap:Body>
                <nfeRetAutorizacaoResponse>
                    <nfeRetAutorizacaoResult>
                        <retConsReciNFe versao="4.00">
                            <tpAmb>2</tpAmb>
                            <verAplic>SP_NFE_PL_008i2</verAplic>
                            <nRec>351000000000123</nRec>
                            <cStat>104</cStat>
                            <xMotivo>Lote processado</xMotivo>
                            <protNFe versao="4.00">
                                <infProt>
                                    <tpAmb>2</tpAmb>
                                    <verAplic>SP_NFE_PL_008i2</verAplic>
                                    <chNFe>35240111222333000181650010000000011234567890</chNFe>
                                    <dhRecbto>2024-01-15T10:30:00-03:00</dhRecbto>
                                    <nProt>135240000000123</nProt>
                                    <digVal>abcdef1234567890</digVal>
                                    <cStat>100</cStat>
                                    <xMotivo>Autorizado o uso da NF-e</xMotivo>
                                </infProt>
                            </protNFe>
                        </retConsReciNFe>
                    </nfeRetAutorizacaoResult>
                </nfeRetAutorizacaoResponse>
            </soap:Body>
        </soap:Envelope>';
        
        $this->mockHandler->append(new Response(200, [], $receiptResponse));
        
        $result = $this->sefazService->queryReceipt('351000000000123');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('100', $result['status_code']);
        $this->assertEquals('Autorizado o uso da NF-e', $result['message']);
        $this->assertEquals('135240000000123', $result['protocol_number']);
        $this->assertEquals('35240111222333000181650010000000011234567890', $result['access_key']);
    }

    /**
     * Testa o cancelamento de NFCe
     */
    public function test_cancels_nfce_successfully(): void
    {
        // Mock da resposta de cancelamento bem-sucedido
        $cancelResponse = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
            <soap:Body>
                <nfeCancelamentoResponse>
                    <nfeCancelamentoResult>
                        <retEventoNFe versao="1.00">
                            <infEvento>
                                <tpAmb>2</tpAmb>
                                <verAplic>SP_NFE_PL_008i2</verAplic>
                                <cOrgao>35</cOrgao>
                                <cStat>135</cStat>
                                <xMotivo>Evento registrado e vinculado a NF-e</xMotivo>
                                <chNFe>35240111222333000181650010000000011234567890</chNFe>
                                <tpEvento>110111</tpEvento>
                                <xEvento>Cancelamento</xEvento>
                                <nSeqEvento>1</nSeqEvento>
                                <dhRegEvento>2024-01-15T11:00:00-03:00</dhRegEvento>
                                <nProt>135240000000124</nProt>
                            </infEvento>
                        </retEventoNFe>
                    </nfeCancelamentoResult>
                </nfeCancelamentoResponse>
            </soap:Body>
        </soap:Envelope>';
        
        $this->mockHandler->append(new Response(200, [], $cancelResponse));
        
        $accessKey = '35240111222333000181650010000000011234567890';
        $protocolNumber = '135240000000123';
        $justification = 'Cancelamento por erro operacional';
        
        $result = $this->sefazService->cancel($accessKey, $protocolNumber, $justification);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('135', $result['status_code']);
        $this->assertEquals('Evento registrado e vinculado a NF-e', $result['message']);
        $this->assertEquals('135240000000124', $result['protocol_number']);
    }

    /**
     * Testa a consulta de situação da NFCe
     */
    public function test_queries_nfce_status(): void
    {
        // Mock da resposta de consulta de situação
        $statusResponse = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
            <soap:Body>
                <nfeConsultaProtocoloResponse>
                    <nfeConsultaProtocoloResult>
                        <retConsSitNFe versao="4.00">
                            <tpAmb>2</tpAmb>
                            <verAplic>SP_NFE_PL_008i2</verAplic>
                            <cStat>100</cStat>
                            <xMotivo>Autorizado o uso da NF-e</xMotivo>
                            <cUF>35</cUF>
                            <dhRecbto>2024-01-15T10:30:00-03:00</dhRecbto>
                            <chNFe>35240111222333000181650010000000011234567890</chNFe>
                            <protNFe versao="4.00">
                                <infProt>
                                    <tpAmb>2</tpAmb>
                                    <verAplic>SP_NFE_PL_008i2</verAplic>
                                    <chNFe>35240111222333000181650010000000011234567890</chNFe>
                                    <dhRecbto>2024-01-15T10:30:00-03:00</dhRecbto>
                                    <nProt>135240000000123</nProt>
                                    <digVal>abcdef1234567890</digVal>
                                    <cStat>100</cStat>
                                    <xMotivo>Autorizado o uso da NF-e</xMotivo>
                                </infProt>
                            </protNFe>
                        </retConsSitNFe>
                    </nfeConsultaProtocoloResult>
                </nfeConsultaProtocoloResponse>
            </soap:Body>
        </soap:Envelope>';
        
        $this->mockHandler->append(new Response(200, [], $statusResponse));
        
        $accessKey = '35240111222333000181650010000000011234567890';
        $result = $this->sefazService->queryStatus($accessKey);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('100', $result['status_code']);
        $this->assertEquals('Autorizado o uso da NF-e', $result['message']);
        $this->assertEquals('135240000000123', $result['protocol_number']);
    }

    /**
     * Testa o tratamento de erro de conexão
     */
    public function test_handles_connection_error(): void
    {
        // Mock de erro de conexão
        $this->mockHandler->append(new Response(500, [], 'Internal Server Error'));
        
        $xmlNfce = $this->createSampleNfceXml();
        $result = $this->sefazService->authorize($xmlNfce);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Erro de comunicação', $result['message']);
    }

    /**
     * Testa a validação de ambiente (homologação/produção)
     */
    public function test_validates_environment_configuration(): void
    {
        $this->assertEquals('homologacao', $this->sefazService->getEnvironment());
        $this->assertTrue($this->sefazService->isHomologation());
        $this->assertFalse($this->sefazService->isProduction());
    }

    /**
     * Cria um XML de NFCe de exemplo para testes
     */
    private function createSampleNfceXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <NFe xmlns="http://www.portalfiscal.inf.br/nfe">
            <infNFe versao="4.00" Id="NFe35240111222333000181650010000000011234567890">
                <ide>
                    <cUF>35</cUF>
                    <cNF>12345678</cNF>
                    <natOp>Venda</natOp>
                    <mod>65</mod>
                    <serie>1</serie>
                    <nNF>1</nNF>
                    <dhEmi>2024-01-15T10:00:00-03:00</dhEmi>
                    <tpNF>1</tpNF>
                    <idDest>1</idDest>
                    <cMunFG>3550308</cMunFG>
                    <tpImp>4</tpImp>
                    <tpEmis>1</tpEmis>
                    <cDV>0</cDV>
                    <tpAmb>2</tpAmb>
                    <finNFe>1</finNFe>
                    <indFinal>1</indFinal>
                    <indPres>1</indPres>
                    <procEmi>0</procEmi>
                    <verProc>1.0</verProc>
                </ide>
                <emit>
                    <CNPJ>11222333000181</CNPJ>
                    <xNome>EMPRESA TESTE LTDA</xNome>
                    <enderEmit>
                        <xLgr>Rua Teste</xLgr>
                        <nro>123</nro>
                        <xBairro>Centro</xBairro>
                        <cMun>3550308</cMun>
                        <xMun>São Paulo</xMun>
                        <UF>SP</UF>
                        <CEP>01234567</CEP>
                    </enderEmit>
                    <IE>123456789</IE>
                    <CRT>1</CRT>
                </emit>
                <det nItem="1">
                    <prod>
                        <cProd>PROD001</cProd>
                        <cEAN/>
                        <xProd>Produto Teste</xProd>
                        <NCM>12345678</NCM>
                        <CFOP>5102</CFOP>
                        <uCom>UN</uCom>
                        <qCom>1.0000</qCom>
                        <vUnCom>10.50</vUnCom>
                        <vProd>10.50</vProd>
                        <cEANTrib/>
                        <uTrib>UN</uTrib>
                        <qTrib>1.0000</qTrib>
                        <vUnTrib>10.50</vUnTrib>
                        <indTot>1</indTot>
                    </prod>
                    <imposto>
                        <ICMS>
                            <ICMSSN102>
                                <orig>0</orig>
                                <CSOSN>102</CSOSN>
                            </ICMSSN102>
                        </ICMS>
                    </imposto>
                </det>
                <total>
                    <ICMSTot>
                        <vBC>0.00</vBC>
                        <vICMS>0.00</vICMS>
                        <vICMSDeson>0.00</vICMSDeson>
                        <vFCP>0.00</vFCP>
                        <vBCST>0.00</vBCST>
                        <vST>0.00</vST>
                        <vFCPST>0.00</vFCPST>
                        <vFCPSTRet>0.00</vFCPSTRet>
                        <vProd>10.50</vProd>
                        <vFrete>0.00</vFrete>
                        <vSeg>0.00</vSeg>
                        <vDesc>0.00</vDesc>
                        <vII>0.00</vII>
                        <vIPI>0.00</vIPI>
                        <vIPIDevol>0.00</vIPIDevol>
                        <vPIS>0.00</vPIS>
                        <vCOFINS>0.00</vCOFINS>
                        <vOutro>0.00</vOutro>
                        <vNF>10.50</vNF>
                    </ICMSTot>
                </total>
                <transp>
                    <modFrete>9</modFrete>
                </transp>
                <pag>
                    <detPag>
                        <tPag>01</tPag>
                        <vPag>10.50</vPag>
                    </detPag>
                </pag>
                <infAdic>
                    <infCpl>Documento emitido por ME ou EPP optante pelo Simples Nacional</infCpl>
                </infAdic>
            </infNFe>
        </NFe>';
    }
}