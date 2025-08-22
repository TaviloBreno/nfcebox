<?php

namespace Tests\Unit\Services\Fiscal;

use App\Models\CompanyConfig;
use App\Models\Sale;
use App\Services\Fiscal\SefazClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use ReflectionClass;
use stdClass;

class SefazClientTest extends TestCase
{
    use RefreshDatabase;

    private SefazClient $sefazClient;
    private $mockSoapClient;
    private $mockResponse;

    protected function setUp(): void
    {
        parent::setUp();
        
        $companyConfig = CompanyConfig::factory()->create([
            'environment' => 'homologacao'
        ]);
        
        $this->sefazClient = new SefazClient($companyConfig);
        $this->mockSoapClient = $this->createMock(stdClass::class);
        $this->mockResponse = new stdClass();
    }

    public function test_processes_authorization_response_correctly()
    {
        $sale = Sale::factory()->create(['status' => 'draft']);
        $responseXml = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:nfe="http://www.portalfiscal.inf.br/nfe">
            <soap:Body>
                <nfe:nfeResultMsg>
                    <nfe:retEnviNFe>
                        <nfe:tpAmb>2</nfe:tpAmb>
                        <nfe:cUF>35</nfe:cUF>
                        <nfe:verAplic>SP_NFE_PL_008i2</nfe:verAplic>
                        <nfe:cStat>103</nfe:cStat>
                        <nfe:xMotivo>Lote recebido com sucesso</nfe:xMotivo>
                        <nfe:dhRecbto>2025-08-22T00:50:00-03:00</nfe:dhRecbto>
                        <nfe:protNFe>
                            <nfe:infProt>
                                <nfe:tpAmb>2</nfe:tpAmb>
                                <nfe:verAplic>SP_NFE_PL_008i2</nfe:verAplic>
                                <nfe:chNFe>35250811222333000181650010000000011234567890</nfe:chNFe>
                                <nfe:dhRecbto>2025-08-22T00:50:00-03:00</nfe:dhRecbto>
                                <nfe:nProt>135250000123456</nfe:nProt>
                                <nfe:digVal>abcd1234</nfe:digVal>
                                <nfe:cStat>100</nfe:cStat>
                                <nfe:xMotivo>Autorizado o uso da NF-e</nfe:xMotivo>
                            </nfe:infProt>
                        </nfe:protNFe>
                    </nfe:retEnviNFe>
                </nfe:nfeResultMsg>
            </soap:Body>
        </soap:Envelope>';
        
        $reflection = new \ReflectionClass($this->sefazClient);
        $method = $reflection->getMethod('processAuthorizationResponse');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->sefazClient, $responseXml, $sale);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('processing', $result['status']);
        $this->assertEquals('Lote recebido com sucesso', $result['message']);
    }

    public function test_processes_query_response_correctly()
    {
        $sale = Sale::factory()->create(['status' => 'draft']);
        $responseXml = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:nfe="http://www.portalfiscal.inf.br/nfe">
            <soap:Body>
                <nfe:nfeResultMsg>
                    <nfe:retConsSitNFe>
                        <nfe:tpAmb>2</nfe:tpAmb>
                        <nfe:verAplic>SP_NFE_PL_008i2</nfe:verAplic>
                        <nfe:cStat>104</nfe:cStat>
                        <nfe:xMotivo>Lote processado</nfe:xMotivo>
                        <nfe:cUF>35</nfe:cUF>
                        <nfe:dhRecbto>2025-08-22T00:50:00-03:00</nfe:dhRecbto>
                        <nfe:chNFe>35250811222333000181650010000000011234567890</nfe:chNFe>
                        <nfe:protNFe>
                            <nfe:infProt>
                                <nfe:tpAmb>2</nfe:tpAmb>
                                <nfe:verAplic>SP_NFE_PL_008i2</nfe:verAplic>
                                <nfe:chNFe>35250811222333000181650010000000011234567890</nfe:chNFe>
                                <nfe:dhRecbto>2025-08-22T00:50:00-03:00</nfe:dhRecbto>
                                <nfe:nProt>135250000123456</nfe:nProt>
                                <nfe:digVal>abcd1234</nfe:digVal>
                                <nfe:cStat>100</nfe:cStat>
                                <nfe:xMotivo>Autorizado o uso da NF-e</nfe:xMotivo>
                            </nfe:infProt>
                        </nfe:protNFe>
                    </nfe:retConsSitNFe>
                </nfe:nfeResultMsg>
            </soap:Body>
        </soap:Envelope>';
        
        $reflection = new \ReflectionClass($this->sefazClient);
        $method = $reflection->getMethod('processQueryResponse');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->sefazClient, $responseXml, $sale);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('authorized', $result['status']);
        $this->assertEquals('135250000123456', $result['protocol']);
        $this->assertEquals('35250811222333000181650010000000011234567890', $result['access_key']);
        $this->assertEquals('Autorizado o uso da NF-e', $result['message']);
    }
}