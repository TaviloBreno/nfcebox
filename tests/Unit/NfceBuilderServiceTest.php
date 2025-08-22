<?php

namespace Tests\Unit;

use App\Models\CompanyConfig;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Fiscal\NfceBuilderService;
use App\Services\Fiscal\DigitalSignatureService;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NfceBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyConfig $companyConfig;
    private NfceBuilderService $nfceService;
    private Sale $sale;
    private Customer $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria dados de teste
        $this->createTestData();
        
        // Inicializa o serviço
        $this->nfceService = new NfceBuilderService($this->companyConfig);
    }

    /**
     * Cria dados de teste para homologação
     */
    private function createTestData(): void
    {
        // Empresa de teste (homologação)
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

        // Cria certificado padrão para a empresa
        $this->companyConfig->certificates()->create([
            'alias' => 'Certificado Teste',
            'path' => 'certificates/test.pfx',
            'file_path' => 'certificates/test.pfx',
            'password' => 'senha123',
            'is_default' => true,
            'expires_at' => now()->addYear()
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
            'sku' => 'PROD001',
            'price' => 10.50,
            'stock' => 100.000,
            'unit' => 'UN',
            'ncm' => '12345678',
            'cest' => '1234567',
            'cfop' => '5102'
        ]);

        // Venda de teste
        $this->sale = Sale::create([
            'customer_id' => $this->customer->id,
            'number' => '000001',
            'total' => 21.00,
            'payment_method' => 'cash',
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
        $this->sale->load('saleItems.product');
    }

    /**
     * Testa a geração da chave de acesso
     */
    public function test_generates_access_key(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturnArgument(0);
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        // Usa reflection para acessar método privado
        $reflection = new \ReflectionClass($service);
        $generateAccessKeyMethod = $reflection->getMethod('generateAccessKey');
        $generateAccessKeyMethod->setAccessible(true);
        
        $generateAccessKeyMethod->invoke($service, $this->sale);
        
        $accessKey = $service->getAccessKey();
        
        // Verifica se a chave tem 44 dígitos
        $this->assertEquals(44, strlen($accessKey));
        
        // Verifica se é numérica
        $this->assertTrue(is_numeric($accessKey));
        
        // Verifica código da UF (SP = 35)
        $this->assertEquals('35', substr($accessKey, 0, 2));
        
        // Verifica modelo (65 para NFCe)
        $this->assertEquals('65', substr($accessKey, 20, 2));
    }

    /**
     * Testa o cálculo dos totais
     */
    public function test_calculates_totals(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturnArgument(0);
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        // Usa reflection para acessar método privado
        $reflection = new \ReflectionClass($service);
        $calculateTotalsMethod = $reflection->getMethod('calculateTotals');
        $calculateTotalsMethod->setAccessible(true);
        
        $calculateTotalsMethod->invoke($service, $this->sale);
        
        $totals = $service->getTotals();
        
        // Verifica se os totais foram calculados corretamente
        $this->assertEquals('21.00', $totals['vProd']); // 2 * 10.50
        $this->assertEquals('21.00', $totals['vNF']);
        $this->assertEquals('0.00', $totals['vDesc']);
        $this->assertEquals('0.00', $totals['vICMS']);
    }

    /**
     * Testa a construção do XML básico (sem assinatura)
     */
    public function test_builds_basic_xml_structure(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturnArgument(0);
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        $xml = $service->buildNfceXml($this->sale, $this->customer);
        
        // Verifica se o XML foi gerado
        $this->assertNotEmpty($xml);
        
        // Carrega o XML para validação
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica elementos principais
        $this->assertEquals(1, $xpath->query('//nfe:nfeProc')->length);
        $this->assertEquals(1, $xpath->query('//nfe:NFe')->length);
        $this->assertEquals(1, $xpath->query('//nfe:infNFe')->length);
        $this->assertEquals(1, $xpath->query('//nfe:ide')->length);
        $this->assertEquals(1, $xpath->query('//nfe:emit')->length);
        $this->assertEquals(1, $xpath->query('//nfe:dest')->length);
        $this->assertEquals(1, $xpath->query('//nfe:det')->length);
        $this->assertEquals(1, $xpath->query('//nfe:total')->length);
    }

    /**
     * Testa os dados do emitente no XML
     */
    public function test_xml_contains_correct_emitter_data(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturnArgument(0);
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        $xml = $service->buildNfceXml($this->sale, $this->customer);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica dados do emitente
        $cnpj = $xpath->query('//nfe:emit/nfe:CNPJ')->item(0)->nodeValue;
        $this->assertEquals('11222333000181', $cnpj);
        
        $xNome = $xpath->query('//nfe:emit/nfe:xNome')->item(0)->nodeValue;
        $this->assertEquals('EMPRESA TESTE LTDA', $xNome);
        
        $ie = $xpath->query('//nfe:emit/nfe:IE')->item(0)->nodeValue;
        $this->assertEquals('123456789', $ie);
    }

    /**
     * Testa os dados do destinatário no XML
     */
    public function test_xml_contains_correct_customer_data(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturnArgument(0);
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        $xml = $service->buildNfceXml($this->sale, $this->customer);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica dados do destinatário
        $cpf = $xpath->query('//nfe:dest/nfe:CPF')->item(0)->nodeValue;
        $this->assertEquals('12345678900', $cpf);
        
        $xNome = $xpath->query('//nfe:dest/nfe:xNome')->item(0)->nodeValue;
        $this->assertEquals('Cliente Teste', $xNome);
    }

    /**
     * Testa os dados dos produtos no XML
     */
    public function test_xml_contains_correct_product_data(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturnArgument(0);
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        $xml = $service->buildNfceXml($this->sale, $this->customer);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica dados do produto
        $xProd = $xpath->query('//nfe:det/nfe:prod/nfe:xProd')->item(0)->nodeValue;
        $this->assertEquals('Produto Teste', $xProd);
        
        $ncm = $xpath->query('//nfe:det/nfe:prod/nfe:NCM')->item(0)->nodeValue;
        $this->assertEquals('12345678', $ncm);
        
        $cfop = $xpath->query('//nfe:det/nfe:prod/nfe:CFOP')->item(0)->nodeValue;
        $this->assertEquals('5102', $cfop);
        
        $qCom = $xpath->query('//nfe:det/nfe:prod/nfe:qCom')->item(0)->nodeValue;
        $this->assertEquals('2.0000', $qCom);
        
        $vUnCom = $xpath->query('//nfe:det/nfe:prod/nfe:vUnCom')->item(0)->nodeValue;
        $this->assertEquals('10.50', $vUnCom);
    }

    /**
     * Testa os totais no XML
     */
    public function test_xml_contains_correct_totals(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturnArgument(0);
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        $xml = $service->buildNfceXml($this->sale, $this->customer);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica totais
        $vProd = $xpath->query('//nfe:total/nfe:ICMSTot/nfe:vProd')->item(0)->nodeValue;
        $this->assertEquals('21.00', $vProd);
        
        $vNF = $xpath->query('//nfe:total/nfe:ICMSTot/nfe:vNF')->item(0)->nodeValue;
        $this->assertEquals('21.00', $vNF);
        
        $vDesc = $xpath->query('//nfe:total/nfe:ICMSTot/nfe:vDesc')->item(0)->nodeValue;
        $this->assertEquals('0.00', $vDesc);
    }

    /**
     * Testa a geração da URL do QR Code
     */
    public function test_generates_qr_code_url(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturn(new \DOMDocument());
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        // Gera o XML primeiro para ter a chave de acesso
        $service->buildNfceXml($this->sale, $this->customer);
        
        $qrCodeUrl = $service->generateQrCodeUrl($this->sale);
        
        // Verifica se a URL foi gerada
        $this->assertNotEmpty($qrCodeUrl);
        $this->assertStringContainsString('fazenda.sp.gov.br', $qrCodeUrl);
        $this->assertStringContainsString('chNFe=', $qrCodeUrl);
        $this->assertStringContainsString('tpAmb=2', $qrCodeUrl); // Homologação
    }

    /**
     * Testa o XML sem cliente (consumidor não identificado)
     */
    public function test_builds_xml_without_customer(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturnArgument(0);
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);
        
        $xml = $service->buildNfceXml($this->sale, null);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        // Verifica que não há elemento dest
        $this->assertEquals(0, $xpath->query('//nfe:dest')->length);
        
        // Mas outros elementos devem existir
        $this->assertEquals(1, $xpath->query('//nfe:emit')->length);
        $this->assertEquals(1, $xpath->query('//nfe:det')->length);
    }

    /**
     * Testa a validação de código UF
     */
    public function test_validates_uf_codes(): void
    {
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para acessar método privado
        $reflection = new \ReflectionClass($service);
        $getUfCodeMethod = $reflection->getMethod('getUfCode');
        $getUfCodeMethod->setAccessible(true);
        
        // Testa alguns códigos de UF
        $this->assertEquals('35', $getUfCodeMethod->invoke($service, 'SP'));
        $this->assertEquals('33', $getUfCodeMethod->invoke($service, 'RJ'));
        $this->assertEquals('31', $getUfCodeMethod->invoke($service, 'MG'));
        $this->assertEquals('43', $getUfCodeMethod->invoke($service, 'RS'));
        
        // Testa UF inválida (deve retornar SP como padrão)
        $this->assertEquals('35', $getUfCodeMethod->invoke($service, 'XX'));
    }

    /**
     * Testa o cálculo do dígito verificador da chave de acesso
     */
    public function test_calculates_access_key_check_digit(): void
    {
        // Mock do DigitalSignatureService
        $mockSignatureService = $this->createMock(DigitalSignatureService::class);
        $mockSignatureService->method('signXml')->willReturn(new \DOMDocument());
        
        // Cria o serviço com o mock
        $service = new NfceBuilderService($this->companyConfig);
        
        // Usa reflection para injetar o mock
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('signatureService');
        $property->setAccessible(true);
        $property->setValue($service, $mockSignatureService);

        // Gera o XML primeiro para ter a chave de acesso
        $service->buildNfceXml($this->sale, $this->customer);
        
        $accessKey = $service->getAccessKey();
        
        // Verifica se a chave tem 44 dígitos
        $this->assertEquals(44, strlen($accessKey));
        
        // Verifica o dígito verificador
        $chaveBase = substr($accessKey, 0, 43);
        $dv = substr($accessKey, 43, 1);
        
        // Calcula o DV esperado
        $sequencia = '4329876543298765432987654329876543298765432';
        $soma = 0;
        
        for ($i = 0; $i < 43; $i++) {
            $soma += intval($chaveBase[$i]) * intval($sequencia[$i]);
        }
        
        $resto = $soma % 11;
        $dvEsperado = $resto < 2 ? 0 : 11 - $resto;
        
        $this->assertEquals($dvEsperado, intval($dv));
    }
}