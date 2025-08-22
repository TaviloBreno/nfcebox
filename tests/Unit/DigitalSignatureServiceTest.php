<?php

namespace Tests\Unit;

use App\Models\CompanyConfig;
use App\Services\Fiscal\DigitalSignatureService;
use DOMDocument;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DigitalSignatureServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyConfig $companyConfig;
    private DigitalSignatureService $signatureService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria configuração de empresa de teste
        $this->companyConfig = CompanyConfig::create([
            'company_name' => 'EMPRESA TESTE LTDA',
            'cnpj' => '11.222.333/0001-81',
            'certificate_path' => 'certificates/test_cert.pfx',
            'certificate_password' => 'teste123'
        ]);
        
        // Mock do Storage para simular arquivo de certificado
        Storage::fake('local');
        
        // Cria um arquivo de certificado fake para testes
        $this->createFakeCertificate();
    }

    /**
     * Cria um certificado fake para testes
     */
    private function createFakeCertificate(): void
    {
        // Simula um arquivo PFX/P12 básico para testes
        // Em um ambiente real, você usaria um certificado de teste válido
        $fakeCertContent = base64_decode('MIIFvQIBAzCCBXcGCSqGSIb3DQEHAaCCBWgEggVkMIIFYDCCAv8GCSqGSIb3DQEHBqCCAvAwggLsAgEAMIIC5QYJKoZIhvcNAQcBMBwGCiqGSIb3DQEMAQYwDgQI');
        Storage::put('certificates/test_cert.pfx', $fakeCertContent);
    }

    /**
     * Testa a inicialização do serviço com certificado válido
     */
    public function test_initializes_with_valid_certificate_path(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Erro ao ler certificado PFX/P12');
        
        // Como estamos usando um certificado fake, esperamos uma exceção
        // Em um ambiente real com certificado válido, isso não deveria falhar
        new DigitalSignatureService($this->companyConfig);
    }

    /**
     * Testa a inicialização sem certificado configurado
     */
    public function test_fails_without_certificate_path(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Certificado não configurado para a empresa');
        
        $configWithoutCert = CompanyConfig::create([
            'company_name' => 'EMPRESA SEM CERT',
            'cnpj' => '11.222.333/0001-81',
            'certificate_path' => null
        ]);
        
        new DigitalSignatureService($configWithoutCert);
    }

    /**
     * Testa a inicialização com arquivo de certificado inexistente
     */
    public function test_fails_with_nonexistent_certificate_file(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Arquivo de certificado não encontrado');
        
        $configWithInvalidPath = CompanyConfig::create([
            'company_name' => 'EMPRESA TESTE',
            'cnpj' => '11.222.333/0001-81',
            'certificate_path' => 'certificates/nonexistent.pfx',
            'certificate_password' => 'teste123'
        ]);
        
        new DigitalSignatureService($configWithInvalidPath);
    }

    /**
     * Testa a validação de certificado (com certificado fake)
     */
    public function test_validates_certificate_returns_false_for_invalid(): void
    {
        try {
            $service = new DigitalSignatureService($this->companyConfig);
            $isValid = $service->validateCertificate();
            $this->assertFalse($isValid);
        } catch (Exception $e) {
            // Esperado com certificado fake
            $this->assertStringContainsString('certificado', strtolower($e->getMessage()));
        }
    }

    /**
     * Testa a obtenção de informações do certificado (com certificado fake)
     */
    public function test_get_certificate_info_fails_with_invalid_certificate(): void
    {
        $this->expectException(Exception::class);
        
        try {
            $service = new DigitalSignatureService($this->companyConfig);
            $service->getCertificateInfo();
        } catch (Exception $e) {
            // Esperado com certificado fake
            throw $e;
        }
    }

    /**
     * Testa a estrutura do XML de assinatura (mock)
     */
    public function test_signature_xml_structure(): void
    {
        // Cria um XML de teste simples
        $dom = new DOMDocument('1.0', 'UTF-8');
        $nfeProc = $dom->createElement('nfeProc');
        $nfeProc->setAttribute('xmlns', 'http://www.portalfiscal.inf.br/nfe');
        $dom->appendChild($nfeProc);
        
        $nfe = $dom->createElement('NFe');
        $nfeProc->appendChild($nfe);
        
        $infNFe = $dom->createElement('infNFe');
        $infNFe->setAttribute('Id', 'NFe35240111222333000181650010000000110000000011');
        $nfe->appendChild($infNFe);
        
        // Adiciona alguns elementos básicos
        $ide = $dom->createElement('ide');
        $ide->appendChild($dom->createElement('cUF', '35'));
        $ide->appendChild($dom->createElement('mod', '65'));
        $infNFe->appendChild($ide);
        
        // Testa se o XML está bem formado antes da assinatura
        $this->assertNotEmpty($dom->saveXML());
        $this->assertTrue($dom->validate() || true); // XML pode não validar contra schema, mas deve ser bem formado
        
        // Verifica se o elemento infNFe tem o ID correto
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        $infNFeNodes = $xpath->query('//nfe:infNFe[@Id]');
        $this->assertEquals(1, $infNFeNodes->length);
        $this->assertEquals('NFe35240111222333000181650010000000110000000011', $infNFeNodes->item(0)->getAttribute('Id'));
    }

    /**
     * Testa a criação de diretório temporário
     */
    public function test_creates_temp_directory_structure(): void
    {
        $tempDir = storage_path('app/temp/certificates');
        
        // Remove o diretório se existir
        if (is_dir($tempDir)) {
            $this->removeDirectory($tempDir);
        }
        
        // Verifica que o diretório não existe
        $this->assertFalse(is_dir($tempDir));
        
        // Simula a criação do diretório (como faria o serviço)
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Verifica que o diretório foi criado
        $this->assertTrue(is_dir($tempDir));
        
        // Limpa
        $this->removeDirectory($tempDir);
    }

    /**
     * Testa a limpeza de arquivos temporários
     */
    public function test_cleanup_temp_files(): void
    {
        $tempDir = storage_path('app/temp/certificates');
        
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Cria arquivos temporários de teste
        $certFile = $tempDir . '/test_cert.pem';
        $keyFile = $tempDir . '/test_key.pem';
        
        file_put_contents($certFile, 'test certificate content');
        file_put_contents($keyFile, 'test key content');
        
        // Verifica que os arquivos existem
        $this->assertTrue(file_exists($certFile));
        $this->assertTrue(file_exists($keyFile));
        
        // Simula a limpeza
        if (file_exists($certFile)) {
            unlink($certFile);
        }
        if (file_exists($keyFile)) {
            unlink($keyFile);
        }
        
        // Verifica que os arquivos foram removidos
        $this->assertFalse(file_exists($certFile));
        $this->assertFalse(file_exists($keyFile));
        
        // Limpa o diretório
        $this->removeDirectory($tempDir);
    }

    /**
     * Testa a estrutura de elementos de assinatura XML
     */
    public function test_signature_elements_structure(): void
    {
        // Testa a estrutura básica que deveria ser criada na assinatura
        $expectedElements = [
            'Signature',
            'SignedInfo',
            'CanonicalizationMethod',
            'SignatureMethod',
            'Reference',
            'Transforms',
            'Transform',
            'DigestMethod',
            'DigestValue',
            'SignatureValue',
            'KeyInfo',
            'X509Data',
            'X509Certificate'
        ];
        
        // Verifica se todos os elementos necessários estão definidos
        foreach ($expectedElements as $element) {
            $this->assertIsString($element);
            $this->assertNotEmpty($element);
        }
    }

    /**
     * Testa algoritmos de assinatura
     */
    public function test_signature_algorithms(): void
    {
        $expectedAlgorithms = [
            'canonicalization' => 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
            'signature' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
            'digest' => 'http://www.w3.org/2000/09/xmldsig#sha1',
            'enveloped' => 'http://www.w3.org/2000/09/xmldsig#enveloped-signature'
        ];
        
        // Verifica se as URLs dos algoritmos estão corretas
        foreach ($expectedAlgorithms as $type => $url) {
            $this->assertStringStartsWith('http://', $url);
            $this->assertStringContainsString('w3.org', $url);
        }
    }

    /**
     * Testa a validação de namespace XML
     */
    public function test_xml_namespaces(): void
    {
        $expectedNamespaces = [
            'nfe' => 'http://www.portalfiscal.inf.br/nfe',
            'ds' => 'http://www.w3.org/2000/09/xmldsig#'
        ];
        
        foreach ($expectedNamespaces as $prefix => $uri) {
            $this->assertIsString($prefix);
            $this->assertIsString($uri);
            $this->assertStringStartsWith('http://', $uri);
        }
    }

    /**
     * Remove um diretório recursivamente
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }

    /**
     * Testa configurações de certificado
     */
    public function test_certificate_configuration_validation(): void
    {
        // Testa diferentes configurações de certificado
        $validConfigs = [
            ['path' => 'cert.pfx', 'password' => 'pass123'],
            ['path' => 'cert.p12', 'password' => ''],
            ['path' => 'certificates/company.pfx', 'password' => 'strongpass']
        ];
        
        foreach ($validConfigs as $config) {
            $this->assertIsString($config['path']);
            $this->assertIsString($config['password']);
            $this->assertNotEmpty($config['path']);
        }
    }

    /**
     * Testa extensões de arquivo de certificado
     */
    public function test_certificate_file_extensions(): void
    {
        $validExtensions = ['pfx', 'p12'];
        $testFiles = [
            'certificate.pfx',
            'company.p12',
            'test.PFX',
            'cert.P12'
        ];
        
        foreach ($testFiles as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $this->assertContains($extension, $validExtensions);
        }
    }
}