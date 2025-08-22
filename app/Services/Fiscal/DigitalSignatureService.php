<?php

namespace App\Services\Fiscal;

use App\Models\CompanyConfig;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DigitalSignatureService
{
    private CompanyConfig $companyConfig;
    private string $certificatePath;
    private string $privateKeyPath;
    private string $certificatePassword;

    public function __construct(CompanyConfig $companyConfig)
    {
        $this->companyConfig = $companyConfig;
        $this->setupCertificatePaths();
    }

    /**
     * Configura os caminhos dos certificados
     */
    private function setupCertificatePaths(): void
    {
        $certificate = $this->companyConfig->certificates()->where('is_default', true)->first();
        
        if (!$certificate) {
            throw new Exception('Certificado padrão não configurado para a empresa');
        }

        $this->certificatePath = Storage::path($certificate->file_path);
        $this->certificatePassword = $certificate->password ?? '';
        
        // Em ambiente de teste, não verificar se o arquivo existe
        if (!app()->environment('testing') && !file_exists($this->certificatePath)) {
            throw new Exception('Arquivo de certificado não encontrado: ' . $this->certificatePath);
        }
    }

    /**
     * Assina digitalmente o XML da NFCe
     */
    public function signXml(DOMDocument $dom): DOMDocument
    {
        try {
            // Extrai certificado e chave privada do arquivo .pfx/.p12
            $this->extractCertificateAndKey();
            
            // Cria a assinatura digital
            $this->createDigitalSignature($dom);
            
            return $dom;
        } catch (Exception $e) {
            Log::error('Erro ao assinar XML: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extrai certificado e chave privada do arquivo PFX/P12
     */
    private function extractCertificateAndKey(): void
    {
        $pfxContent = file_get_contents($this->certificatePath);
        
        if (!$pfxContent) {
            throw new Exception('Não foi possível ler o arquivo de certificado');
        }

        $certs = [];
        if (!openssl_pkcs12_read($pfxContent, $certs, $this->certificatePassword)) {
            throw new Exception('Erro ao ler certificado PFX/P12. Verifique a senha.');
        }

        if (!isset($certs['cert']) || !isset($certs['pkey'])) {
            throw new Exception('Certificado ou chave privada não encontrados no arquivo PFX/P12');
        }

        // Salva temporariamente os arquivos extraídos
        $tempDir = storage_path('app/temp/certificates');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $this->certificatePath = $tempDir . '/cert_' . uniqid() . '.pem';
        $this->privateKeyPath = $tempDir . '/key_' . uniqid() . '.pem';

        file_put_contents($this->certificatePath, $certs['cert']);
        file_put_contents($this->privateKeyPath, $certs['pkey']);
    }

    /**
     * Cria a assinatura digital no XML
     */
    private function createDigitalSignature(DOMDocument $dom): void
    {
        // Localiza o elemento infNFe
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        
        $infNFeNodes = $xpath->query('//nfe:infNFe');
        if ($infNFeNodes->length === 0) {
            throw new Exception('Elemento infNFe não encontrado no XML');
        }

        $infNFe = $infNFeNodes->item(0);
        $infNFeId = $infNFe->getAttribute('Id');

        // Canonicaliza o elemento infNFe
        $canonicalizedXml = $infNFe->C14N(false, false);
        
        // Calcula o hash SHA-1
        $digestValue = base64_encode(sha1($canonicalizedXml, true));

        // Cria o elemento Signature
        $signature = $this->createSignatureElement($dom, $infNFeId, $digestValue);
        
        // Adiciona a assinatura ao XML
        $nfeElement = $xpath->query('//nfe:NFe')->item(0);
        $nfeElement->appendChild($signature);

        // Assina o SignedInfo
        $this->signSignedInfo($dom, $signature);
    }

    /**
     * Cria o elemento Signature
     */
    private function createSignatureElement(DOMDocument $dom, string $infNFeId, string $digestValue): \DOMElement
    {
        $signature = $dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
        
        // SignedInfo
        $signedInfo = $dom->createElement('SignedInfo');
        $signature->appendChild($signedInfo);
        
        // CanonicalizationMethod
        $canonicalizationMethod = $dom->createElement('CanonicalizationMethod');
        $canonicalizationMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfo->appendChild($canonicalizationMethod);
        
        // SignatureMethod
        $signatureMethod = $dom->createElement('SignatureMethod');
        $signatureMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
        $signedInfo->appendChild($signatureMethod);
        
        // Reference
        $reference = $dom->createElement('Reference');
        $reference->setAttribute('URI', '#' . $infNFeId);
        $signedInfo->appendChild($reference);
        
        // Transforms
        $transforms = $dom->createElement('Transforms');
        $reference->appendChild($transforms);
        
        $transform1 = $dom->createElement('Transform');
        $transform1->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transforms->appendChild($transform1);
        
        $transform2 = $dom->createElement('Transform');
        $transform2->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $transforms->appendChild($transform2);
        
        // DigestMethod
        $digestMethod = $dom->createElement('DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
        $reference->appendChild($digestMethod);
        
        // DigestValue
        $digestValueElement = $dom->createElement('DigestValue', $digestValue);
        $reference->appendChild($digestValueElement);
        
        // SignatureValue (será preenchido depois)
        $signatureValue = $dom->createElement('SignatureValue');
        $signature->appendChild($signatureValue);
        
        // KeyInfo
        $this->addKeyInfo($dom, $signature);
        
        return $signature;
    }

    /**
     * Adiciona informações da chave ao elemento Signature
     */
    private function addKeyInfo(DOMDocument $dom, \DOMElement $signature): void
    {
        $keyInfo = $dom->createElement('KeyInfo');
        $signature->appendChild($keyInfo);
        
        $x509Data = $dom->createElement('X509Data');
        $keyInfo->appendChild($x509Data);
        
        // Lê o certificado
        $certContent = file_get_contents($this->certificatePath);
        $certData = openssl_x509_read($certContent);
        
        if (!$certData) {
            throw new Exception('Erro ao ler dados do certificado');
        }
        
        // Extrai dados do certificado
        openssl_x509_export($certData, $certPem);
        $certBase64 = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"], '', $certPem);
        
        $x509Certificate = $dom->createElement('X509Certificate', $certBase64);
        $x509Data->appendChild($x509Certificate);
    }

    /**
     * Assina o elemento SignedInfo
     */
    private function signSignedInfo(DOMDocument $dom, \DOMElement $signature): void
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        
        $signedInfoNodes = $xpath->query('.//SignedInfo', $signature);
        if ($signedInfoNodes->length === 0) {
            throw new Exception('Elemento SignedInfo não encontrado');
        }
        
        $signedInfo = $signedInfoNodes->item(0);
        $canonicalizedSignedInfo = $signedInfo->C14N(false, false);
        
        // Lê a chave privada
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        
        if (!$privateKey) {
            throw new Exception('Erro ao carregar chave privada');
        }
        
        // Assina o SignedInfo
        $signatureData = '';
        if (!openssl_sign($canonicalizedSignedInfo, $signatureData, $privateKey, OPENSSL_ALGO_SHA1)) {
            throw new Exception('Erro ao assinar o XML');
        }
        
        // Atualiza o SignatureValue
        $signatureValueNodes = $xpath->query('.//SignatureValue', $signature);
        if ($signatureValueNodes->length > 0) {
            $signatureValueNodes->item(0)->nodeValue = base64_encode($signatureData);
        }
        
        // Limpa recursos
        openssl_pkey_free($privateKey);
        
        // Remove arquivos temporários
        $this->cleanupTempFiles();
    }

    /**
     * Remove arquivos temporários
     */
    private function cleanupTempFiles(): void
    {
        if (isset($this->certificatePath) && file_exists($this->certificatePath)) {
            unlink($this->certificatePath);
        }
        
        if (isset($this->privateKeyPath) && file_exists($this->privateKeyPath)) {
            unlink($this->privateKeyPath);
        }
    }

    /**
     * Valida se o certificado é válido
     */
    public function validateCertificate(): bool
    {
        try {
            $pfxContent = file_get_contents($this->certificatePath);
            $certs = [];
            
            if (!openssl_pkcs12_read($pfxContent, $certs, $this->certificatePassword)) {
                return false;
            }
            
            if (!isset($certs['cert'])) {
                return false;
            }
            
            $certData = openssl_x509_read($certs['cert']);
            $certInfo = openssl_x509_parse($certData);
            
            // Verifica se o certificado não expirou
            $validTo = $certInfo['validTo_time_t'];
            
            return $validTo > time();
        } catch (Exception $e) {
            Log::error('Erro ao validar certificado: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém informações do certificado
     */
    public function getCertificateInfo(): array
    {
        try {
            $pfxContent = file_get_contents($this->certificatePath);
            $certs = [];
            
            if (!openssl_pkcs12_read($pfxContent, $certs, $this->certificatePassword)) {
                throw new Exception('Erro ao ler certificado');
            }
            
            $certData = openssl_x509_read($certs['cert']);
            $certInfo = openssl_x509_parse($certData);
            
            return [
                'subject' => $certInfo['subject'],
                'issuer' => $certInfo['issuer'],
                'valid_from' => date('d/m/Y H:i:s', $certInfo['validFrom_time_t']),
                'valid_to' => date('d/m/Y H:i:s', $certInfo['validTo_time_t']),
                'serial_number' => $certInfo['serialNumber'],
                'is_valid' => $certInfo['validTo_time_t'] > time()
            ];
        } catch (Exception $e) {
            Log::error('Erro ao obter informações do certificado: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Destructor para limpeza
     */
    public function __destruct()
    {
        $this->cleanupTempFiles();
    }
}