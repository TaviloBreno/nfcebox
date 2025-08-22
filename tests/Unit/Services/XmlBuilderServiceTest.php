<?php

namespace Tests\Unit\Services;

use App\Models\CompanyConfig;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Fiscal\XmlBuilderService;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XmlBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyConfig $companyConfig;
    private XmlBuilderService $xmlBuilder;
    private Sale $sale;
    private Customer $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTestData();
        $this->xmlBuilder = new XmlBuilderService();
    }

    /**
     * Cria dados de teste
     */
    private function createTestData(): void
    {
        // Empresa de teste
        $this->companyConfig = CompanyConfig::create([
            'corporate_name' => 'EMPRESA TESTE LTDA',
            'trade_name' => 'Loja Teste',
            'cnpj' => '11222333000181',
            'ie' => '123456789',
            'im' => '12345',
            'address_json' => json_encode([
                'street' => 'Rua Teste',
                'number' => '123',
                'complement' => 'Sala 1',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'city_code' => '3550308',
                'state' => 'SP',
                'zip_code' => '01234567',
                'phone' => '(11) 1234-5678',
                'email' => 'teste@empresa.com.br'
            ]),
            'environment' => 'homologacao',
            'nfce_series' => 1,
            'nfce_number' => 1,
            'csc_id' => '000001',
            'csc_token' => 'ABCDEF123456789'
        ]);

        // Cliente de teste
        $this->customer = Customer::create([
            'name' => 'Cliente Teste',
            'document' => '12345678900',
            'email' => 'cliente@teste.com.br',
            'phone' => '(11) 9876-5432',
            'address' => json_encode([
                'street' => 'Rua Cliente',
                'number' => '456',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234567'
            ])
        ]);

        // Produto de teste
        $this->product = Product::create([
            'name' => 'Produto Teste',
            'description' => 'Descrição do produto teste',
            'code' => 'PROD001',
            'price' => 10.50,
            'stock_qty' => 100.000,
            'unit' => 'UN',
            'ncm' => '12345678',
            'cest' => '1234567',
            'cfop' => '5102'
        ]);

        // Venda de teste
        $this->sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'draft'
        ]);

        // Item da venda
        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $this->product->id,
            'qty' => 2.000,
            'unit_price' => 10.50,
            'total' => 21.00
        ]);

        // Recarrega a venda com os itens
        $this->sale->load('saleItems.product', 'customer');
    }

    /**
     * Testa a construção do elemento IDE (Identificação)
     */
    public function test_builds_ide_element(): void
    {
        $xml = $this->xmlBuilder->buildIdeElement($this->sale, $this->companyConfig);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        
        // Verifica elementos obrigatórios
        $this->assertEquals('35', $xpath->query('//cUF')->item(0)->nodeValue); // SP
        $this->assertEquals('65', $xpath->query('//mod')->item(0)->nodeValue); // NFCe
        $this->assertEquals('1', $xpath->query('//serie')->item(0)->nodeValue);
        $this->assertEquals('1', $xpath->query('//nNF')->item(0)->nodeValue);
        $this->assertEquals('Venda', $xpath->query('//natOp')->item(0)->nodeValue);
        $this->assertEquals('1', $xpath->query('//tpNF')->item(0)->nodeValue); // Saída
        $this->assertEquals('1', $xpath->query('//idDest')->item(0)->nodeValue); // Operação interna
        $this->assertEquals('3550308', $xpath->query('//cMunFG')->item(0)->nodeValue); // São Paulo
        $this->assertEquals('4', $xpath->query('//tpImp')->item(0)->nodeValue); // DANFE NFCe
        $this->assertEquals('1', $xpath->query('//tpEmis')->item(0)->nodeValue); // Normal
        $this->assertEquals('2', $xpath->query('//tpAmb')->item(0)->nodeValue); // Homologação
        $this->assertEquals('1', $xpath->query('//finNFe')->item(0)->nodeValue); // Normal
        $this->assertEquals('1', $xpath->query('//indFinal')->item(0)->nodeValue); // Consumidor final
        $this->assertEquals('1', $xpath->query('//indPres')->item(0)->nodeValue); // Presencial
    }

    /**
     * Testa a construção do elemento EMIT (Emitente)
     */
    public function test_builds_emit_element(): void
    {
        $xml = $this->xmlBuilder->buildEmitElement($this->companyConfig);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        
        // Verifica dados do emitente
        $this->assertEquals('11222333000181', $xpath->query('//CNPJ')->item(0)->nodeValue);
        $this->assertEquals('EMPRESA TESTE LTDA', $xpath->query('//xNome')->item(0)->nodeValue);
        $this->assertEquals('Loja Teste', $xpath->query('//xFant')->item(0)->nodeValue);
        $this->assertEquals('123456789', $xpath->query('//IE')->item(0)->nodeValue);
        $this->assertEquals('12345', $xpath->query('//IM')->item(0)->nodeValue);
        $this->assertEquals('1', $xpath->query('//CRT')->item(0)->nodeValue); // Simples Nacional
        
        // Verifica endereço
        $this->assertEquals('Rua Teste', $xpath->query('//enderEmit/xLgr')->item(0)->nodeValue);
        $this->assertEquals('123', $xpath->query('//enderEmit/nro')->item(0)->nodeValue);
        $this->assertEquals('Sala 1', $xpath->query('//enderEmit/xCpl')->item(0)->nodeValue);
        $this->assertEquals('Centro', $xpath->query('//enderEmit/xBairro')->item(0)->nodeValue);
        $this->assertEquals('3550308', $xpath->query('//enderEmit/cMun')->item(0)->nodeValue);
        $this->assertEquals('São Paulo', $xpath->query('//enderEmit/xMun')->item(0)->nodeValue);
        $this->assertEquals('SP', $xpath->query('//enderEmit/UF')->item(0)->nodeValue);
        $this->assertEquals('01234567', $xpath->query('//enderEmit/CEP')->item(0)->nodeValue);
        $this->assertEquals('1155', $xpath->query('//enderEmit/cPais')->item(0)->nodeValue); // Brasil
        $this->assertEquals('BRASIL', $xpath->query('//enderEmit/xPais')->item(0)->nodeValue);
        $this->assertEquals('1134567890', $xpath->query('//enderEmit/fone')->item(0)->nodeValue);
    }

    /**
     * Testa a construção do elemento DEST (Destinatário)
     */
    public function test_builds_dest_element(): void
    {
        $xml = $this->xmlBuilder->buildDestElement($this->customer);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        
        // Verifica dados do destinatário
        $this->assertEquals('12345678900', $xpath->query('//CPF')->item(0)->nodeValue);
        $this->assertEquals('Cliente Teste', $xpath->query('//xNome')->item(0)->nodeValue);
        $this->assertEquals('cliente@teste.com.br', $xpath->query('//email')->item(0)->nodeValue);
        
        // Verifica endereço
        $this->assertEquals('Rua Cliente', $xpath->query('//enderDest/xLgr')->item(0)->nodeValue);
        $this->assertEquals('456', $xpath->query('//enderDest/nro')->item(0)->nodeValue);
        $this->assertEquals('São Paulo', $xpath->query('//enderDest/xMun')->item(0)->nodeValue);
        $this->assertEquals('SP', $xpath->query('//enderDest/UF')->item(0)->nodeValue);
        $this->assertEquals('01234567', $xpath->query('//enderDest/CEP')->item(0)->nodeValue);
        $this->assertEquals('1155', $xpath->query('//enderDest/cPais')->item(0)->nodeValue);
        $this->assertEquals('BRASIL', $xpath->query('//enderDest/xPais')->item(0)->nodeValue);
        $this->assertEquals('11987654321', $xpath->query('//enderDest/fone')->item(0)->nodeValue);
    }

    /**
     * Testa a construção do elemento DET (Detalhes dos produtos)
     */
    public function test_builds_det_elements(): void
    {
        $xml = $this->xmlBuilder->buildDetElements($this->sale->saleItems);
        
        $dom = new DOMDocument();
        $dom->loadXML('<root>' . $xml . '</root>');
        
        $xpath = new DOMXPath($dom);
        
        // Verifica se há um item
        $this->assertEquals(1, $xpath->query('//det')->length);
        
        // Verifica dados do produto
        $this->assertEquals('PROD001', $xpath->query('//prod/cProd')->item(0)->nodeValue);
        $this->assertEquals('Produto Teste', $xpath->query('//prod/xProd')->item(0)->nodeValue);
        $this->assertEquals('12345678', $xpath->query('//prod/NCM')->item(0)->nodeValue);
        $this->assertEquals('1234567', $xpath->query('//prod/CEST')->item(0)->nodeValue);
        $this->assertEquals('5102', $xpath->query('//prod/CFOP')->item(0)->nodeValue);
        $this->assertEquals('UN', $xpath->query('//prod/uCom')->item(0)->nodeValue);
        $this->assertEquals('2.0000', $xpath->query('//prod/qCom')->item(0)->nodeValue);
        $this->assertEquals('10.50', $xpath->query('//prod/vUnCom')->item(0)->nodeValue);
        $this->assertEquals('21.00', $xpath->query('//prod/vProd')->item(0)->nodeValue);
        
        // Verifica impostos (ICMS Simples Nacional)
        $this->assertEquals('0', $xpath->query('//imposto/ICMS/ICMSSN102/orig')->item(0)->nodeValue);
        $this->assertEquals('102', $xpath->query('//imposto/ICMS/ICMSSN102/CSOSN')->item(0)->nodeValue);
    }

    /**
     * Testa a construção do elemento TOTAL
     */
    public function test_builds_total_element(): void
    {
        $xml = $this->xmlBuilder->buildTotalElement($this->sale);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        
        // Verifica totais
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vBC')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vICMS')->item(0)->nodeValue);
        $this->assertEquals('21.00', $xpath->query('//ICMSTot/vProd')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vFrete')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vSeg')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vDesc')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vII')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vIPI')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vPIS')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vCOFINS')->item(0)->nodeValue);
        $this->assertEquals('0.00', $xpath->query('//ICMSTot/vOutro')->item(0)->nodeValue);
        $this->assertEquals('21.00', $xpath->query('//ICMSTot/vNF')->item(0)->nodeValue);
    }

    /**
     * Testa a construção do elemento PAG (Pagamento)
     */
    public function test_builds_pag_element(): void
    {
        $xml = $this->xmlBuilder->buildPagElement($this->sale);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        
        // Verifica forma de pagamento
        $this->assertEquals('01', $xpath->query('//detPag/tPag')->item(0)->nodeValue); // Dinheiro
        $this->assertEquals('21.00', $xpath->query('//detPag/vPag')->item(0)->nodeValue);
    }

    /**
     * Testa a construção do elemento INFADIC (Informações Adicionais)
     */
    public function test_builds_infadic_element(): void
    {
        $xml = $this->xmlBuilder->buildInfAdicElement($this->sale, $this->companyConfig);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        
        // Verifica informações complementares
        $infCpl = $xpath->query('//infCpl')->item(0)->nodeValue;
        $this->assertStringContainsString('Documento emitido por ME ou EPP optante pelo Simples Nacional', $infCpl);
        $this->assertStringContainsString('Venda: 000001', $infCpl);
    }

    /**
     * Testa a construção do XML de cancelamento
     */
    public function test_builds_cancellation_xml(): void
    {
        $accessKey = '35240111222333000181650010000000011234567890';
        $protocolNumber = '135240000000123';
        $justification = 'Cancelamento por erro operacional';
        $sequenceNumber = 1;
        
        $xml = $this->xmlBuilder->buildCancellationXml(
            $accessKey,
            $protocolNumber,
            $justification,
            $sequenceNumber,
            $this->companyConfig
        );
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('env', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica estrutura do evento
        $this->assertEquals(1, $xpath->query('//env:envEvento')->length);
        $this->assertEquals(1, $xpath->query('//env:evento')->length);
        $this->assertEquals(1, $xpath->query('//env:infEvento')->length);
        
        // Verifica dados do evento
        $this->assertEquals('35', $xpath->query('//env:cOrgao')->item(0)->nodeValue);
        $this->assertEquals('2', $xpath->query('//env:tpAmb')->item(0)->nodeValue);
        $this->assertEquals('11222333000181', $xpath->query('//env:CNPJ')->item(0)->nodeValue);
        $this->assertEquals($accessKey, $xpath->query('//env:chNFe')->item(0)->nodeValue);
        $this->assertEquals('110111', $xpath->query('//env:tpEvento')->item(0)->nodeValue); // Cancelamento
        $this->assertEquals('1', $xpath->query('//env:nSeqEvento')->item(0)->nodeValue);
        $this->assertEquals('Cancelamento', $xpath->query('//env:xEvento')->item(0)->nodeValue);
        $this->assertEquals($justification, $xpath->query('//env:xJust')->item(0)->nodeValue);
        $this->assertEquals($protocolNumber, $xpath->query('//env:nProt')->item(0)->nodeValue);
    }

    /**
     * Testa a construção do XML de inutilização
     */
    public function test_builds_inutilization_xml(): void
    {
        $series = 1;
        $startNumber = 10;
        $endNumber = 15;
        $justification = 'Inutilização por erro de numeração';
        
        $xml = $this->xmlBuilder->buildInutilizationXml(
            $series,
            $startNumber,
            $endNumber,
            $justification,
            $this->companyConfig
        );
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('inut', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica estrutura da inutilização
        $this->assertEquals(1, $xpath->query('//inut:inutNFe')->length);
        $this->assertEquals(1, $xpath->query('//inut:infInut')->length);
        
        // Verifica dados da inutilização
        $this->assertEquals('35', $xpath->query('//inut:cUF')->item(0)->nodeValue);
        $this->assertEquals('2024', $xpath->query('//inut:ano')->item(0)->nodeValue);
        $this->assertEquals('11222333000181', $xpath->query('//inut:CNPJ')->item(0)->nodeValue);
        $this->assertEquals('65', $xpath->query('//inut:mod')->item(0)->nodeValue); // NFCe
        $this->assertEquals('1', $xpath->query('//inut:serie')->item(0)->nodeValue);
        $this->assertEquals('10', $xpath->query('//inut:nNFIni')->item(0)->nodeValue);
        $this->assertEquals('15', $xpath->query('//inut:nNFFin')->item(0)->nodeValue);
        $this->assertEquals($justification, $xpath->query('//inut:xJust')->item(0)->nodeValue);
    }

    /**
     * Testa a validação de XML contra schema XSD
     */
    public function test_validates_xml_against_schema(): void
    {
        // Constrói XML completo
        $xml = $this->xmlBuilder->buildCompleteNfceXml($this->sale, $this->customer, $this->companyConfig);
        
        // Verifica se o XML é válido
        $dom = new DOMDocument();
        $isValid = $dom->loadXML($xml);
        
        $this->assertTrue($isValid, 'XML deve ser válido');
        
        // Verifica estrutura básica
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        $this->assertEquals(1, $xpath->query('//nfe:NFe')->length);
        $this->assertEquals(1, $xpath->query('//nfe:infNFe')->length);
        $this->assertEquals(1, $xpath->query('//nfe:ide')->length);
        $this->assertEquals(1, $xpath->query('//nfe:emit')->length);
        $this->assertEquals(1, $xpath->query('//nfe:dest')->length);
        $this->assertEquals(1, $xpath->query('//nfe:det')->length);
        $this->assertEquals(1, $xpath->query('//nfe:total')->length);
        $this->assertEquals(1, $xpath->query('//nfe:transp')->length);
        $this->assertEquals(1, $xpath->query('//nfe:pag')->length);
        $this->assertEquals(1, $xpath->query('//nfe:infAdic')->length);
    }

    /**
     * Testa a construção de XML com múltiplos produtos
     */
    public function test_builds_xml_with_multiple_products(): void
    {
        // Cria segundo produto
        $product2 = Product::create([
            'name' => 'Produto Teste 2',
            'description' => 'Segundo produto de teste',
            'code' => 'PROD002',
            'price' => 5.25,
            'stock_qty' => 50.000,
            'unit' => 'UN',
            'ncm' => '87654321',
            'cfop' => '5102'
        ]);

        // Adiciona segundo item à venda
        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $product2->id,
            'qty' => 1.000,
            'unit_price' => 5.25,
            'total' => 5.25
        ]);

        // Atualiza total da venda
        $this->sale->update([
            'subtotal' => 26.25,
            'total' => 26.25
        ]);

        // Recarrega a venda
        $this->sale->load('saleItems.product');
        
        $xml = $this->xmlBuilder->buildDetElements($this->sale->saleItems);
        
        $dom = new DOMDocument();
        $dom->loadXML('<root>' . $xml . '</root>');
        
        $xpath = new DOMXPath($dom);
        
        // Verifica se há dois itens
        $this->assertEquals(2, $xpath->query('//det')->length);
        
        // Verifica numeração dos itens
        $this->assertEquals('1', $xpath->query('//det[@nItem="1"]')->item(0)->getAttribute('nItem'));
        $this->assertEquals('2', $xpath->query('//det[@nItem="2"]')->item(0)->getAttribute('nItem'));
        
        // Verifica dados dos produtos
        $this->assertEquals('PROD001', $xpath->query('//det[@nItem="1"]//prod/cProd')->item(0)->nodeValue);
        $this->assertEquals('PROD002', $xpath->query('//det[@nItem="2"]//prod/cProd')->item(0)->nodeValue);
    }
}