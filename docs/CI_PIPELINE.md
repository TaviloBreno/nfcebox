# Pipeline de CI Local - NFCeBox

Este documento descreve como configurar e executar um pipeline de Integração Contínua (CI) local para o projeto NFCeBox, incluindo testes automatizados, análise de código e relatórios de cobertura.

## 📋 Índice

1. [Visão Geral](#-visão-geral)
2. [Configuração do Ambiente](#-configuração-do-ambiente)
3. [Scripts de Automação](#-scripts-de-automação)
4. [Testes Automatizados](#-testes-automatizados)
5. [Análise de Código](#-análise-de-código)
6. [Cobertura de Código](#-cobertura-de-código)
7. [Hooks do Git](#-hooks-do-git)
8. [Integração com IDEs](#-integração-com-ides)
9. [Troubleshooting](#-troubleshooting)

## 🔍 Visão Geral

### Objetivos do Pipeline
- **Qualidade:** Garantir que o código atende aos padrões de qualidade
- **Confiabilidade:** Executar testes automatizados antes de commits
- **Consistência:** Manter padrões de código em toda a equipe
- **Feedback Rápido:** Identificar problemas rapidamente

### Componentes do Pipeline
1. **Análise Estática:** PHPStan, PHP CS Fixer
2. **Testes Unitários:** PHPUnit com cobertura
3. **Testes de Feature:** Testes de integração
4. **Validação de Dependências:** Composer audit
5. **Formatação de Código:** Padrões PSR-12

## ⚙️ Configuração do Ambiente

### Dependências Necessárias

#### Composer (Desenvolvimento)
```json
{
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.10",
        "friendsofphp/php-cs-fixer": "^3.0",
        "nunomaduro/larastan": "^2.0",
        "pestphp/pest": "^2.0",
        "pestphp/pest-plugin-laravel": "^2.0",
        "spatie/laravel-ignition": "^2.0",
        "fakerphp/faker": "^1.9.1",
        "mockery/mockery": "^1.4.4",
        "nunomaduro/collision": "^7.0"
    }
}
```

#### Instalação
```bash
# Instalar dependências de desenvolvimento
composer install --dev

# Instalar Pest (alternativa ao PHPUnit)
composer require pestphp/pest --dev --with-all-dependencies
./vendor/bin/pest --init
```

### Configuração do PHPUnit

#### phpunit.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         processIsolation="false"
         stopOnFailure="false"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory>./app/Console/Commands</directory>
            <file>./app/Http/Kernel.php</file>
        </exclude>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
    <coverage>
        <report>
            <html outputDirectory="coverage-report" lowUpperBound="50" highLowerBound="80"/>
            <clover outputFile="coverage.xml"/>
            <text outputFile="coverage.txt"/>
        </report>
    </coverage>
</phpunit>
```

### Configuração do PHPStan

#### phpstan.neon
```neon
includes:
    - ./vendor/nunomaduro/larastan/extension.neon

parameters:
    paths:
        - app/
        - database/factories/
        - database/seeders/
    
    level: 8
    
    ignoreErrors:
        - '#PHPDoc tag @var#'
        - '#Call to an undefined method Illuminate\\Database\\Eloquent\\Builder#'
    
    excludePaths:
        - app/Console/Kernel.php
        - app/Http/Kernel.php
        - app/Exceptions/Handler.php
    
    checkMissingIterableValueType: false
    checkGenericClassInNonGenericObjectType: false
    
    tmpDir: storage/phpstan
```

### Configuração do PHP CS Fixer

#### .php-cs-fixer.php
```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database/factories',
        __DIR__ . '/database/seeders',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PHP80Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ],
    ])
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
```

## 🚀 Scripts de Automação

### Composer Scripts

#### composer.json
```json
{
    "scripts": {
        "test": "phpunit",
        "test-coverage": "phpunit --coverage-html coverage-report --coverage-clover coverage.xml",
        "test-unit": "phpunit --testsuite=Unit",
        "test-feature": "phpunit --testsuite=Feature",
        "analyse": "phpstan analyse --memory-limit=2G",
        "format": "php-cs-fixer fix",
        "format-dry": "php-cs-fixer fix --dry-run --diff",
        "ci": [
            "@format-dry",
            "@analyse",
            "@test"
        ],
        "ci-full": [
            "@format-dry",
            "@analyse",
            "@test-coverage",
            "composer audit"
        ],
        "fix": [
            "@format",
            "@analyse",
            "@test"
        ]
    }
}
```

### Scripts PowerShell

#### ci.ps1
```powershell
#!/usr/bin/env pwsh

# Pipeline de CI Local para NFCeBox
# Executa testes, análise de código e verificações de qualidade

param(
    [switch]$Coverage,
    [switch]$Fix,
    [switch]$Verbose
)

# Configurações
$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"

# Cores para output
function Write-Success { param($Message) Write-Host $Message -ForegroundColor Green }
function Write-Error { param($Message) Write-Host $Message -ForegroundColor Red }
function Write-Warning { param($Message) Write-Host $Message -ForegroundColor Yellow }
function Write-Info { param($Message) Write-Host $Message -ForegroundColor Cyan }

# Banner
Write-Host ""
Write-Host "🚀 NFCeBox - Pipeline de CI Local" -ForegroundColor Magenta
Write-Host "======================================" -ForegroundColor Magenta
Write-Host ""

# Verificar dependências
Write-Info "📦 Verificando dependências..."
try {
    if (!(Test-Path "vendor/autoload.php")) {
        Write-Warning "Dependências não encontradas. Instalando..."
        composer install --no-interaction --prefer-dist --optimize-autoloader
    }
    Write-Success "✓ Dependências OK"
} catch {
    Write-Error "✗ Erro ao verificar dependências: $_"
    exit 1
}

# Preparar ambiente de teste
Write-Info "🔧 Preparando ambiente de teste..."
try {
    if (!(Test-Path ".env.testing")) {
        Copy-Item ".env.example" ".env.testing"
        (Get-Content ".env.testing") -replace "APP_ENV=local", "APP_ENV=testing" | Set-Content ".env.testing"
        (Get-Content ".env.testing") -replace "DB_CONNECTION=mysql", "DB_CONNECTION=sqlite" | Set-Content ".env.testing"
        (Get-Content ".env.testing") -replace "DB_DATABASE=nfcebox", "DB_DATABASE=:memory:" | Set-Content ".env.testing"
    }
    
    # Limpar cache
    php artisan config:clear --env=testing
    php artisan cache:clear --env=testing
    
    Write-Success "✓ Ambiente preparado"
} catch {
    Write-Error "✗ Erro ao preparar ambiente: $_"
    exit 1
}

# Análise de código estático
Write-Info "🔍 Executando análise estática (PHPStan)..."
try {
    $phpstanOutput = php ./vendor/bin/phpstan analyse --memory-limit=2G --no-progress 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Success "✓ Análise estática passou"
        if ($Verbose) { Write-Host $phpstanOutput }
    } else {
        Write-Error "✗ Análise estática falhou"
        Write-Host $phpstanOutput
        exit 1
    }
} catch {
    Write-Error "✗ Erro na análise estática: $_"
    exit 1
}

# Verificação de formatação
Write-Info "📝 Verificando formatação de código..."
try {
    $fixerOutput = php ./vendor/bin/php-cs-fixer fix --dry-run --diff --no-interaction 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Success "✓ Formatação OK"
        if ($Verbose) { Write-Host $fixerOutput }
    } else {
        Write-Warning "⚠ Problemas de formatação encontrados"
        Write-Host $fixerOutput
        
        if ($Fix) {
            Write-Info "🔧 Corrigindo formatação..."
            php ./vendor/bin/php-cs-fixer fix --no-interaction
            Write-Success "✓ Formatação corrigida"
        } else {
            Write-Warning "Use -Fix para corrigir automaticamente"
        }
    }
} catch {
    Write-Error "✗ Erro na verificação de formatação: $_"
    exit 1
}

# Testes unitários
Write-Info "🧪 Executando testes unitários..."
try {
    $testCommand = "php ./vendor/bin/phpunit --testsuite=Unit --no-coverage"
    if ($Verbose) { $testCommand += " --verbose" }
    
    $testOutput = Invoke-Expression $testCommand 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Success "✓ Testes unitários passaram"
        if ($Verbose) { Write-Host $testOutput }
    } else {
        Write-Error "✗ Testes unitários falharam"
        Write-Host $testOutput
        exit 1
    }
} catch {
    Write-Error "✗ Erro nos testes unitários: $_"
    exit 1
}

# Testes de feature
Write-Info "🎯 Executando testes de feature..."
try {
    $testCommand = "php ./vendor/bin/phpunit --testsuite=Feature --no-coverage"
    if ($Verbose) { $testCommand += " --verbose" }
    
    $testOutput = Invoke-Expression $testCommand 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Success "✓ Testes de feature passaram"
        if ($Verbose) { Write-Host $testOutput }
    } else {
        Write-Error "✗ Testes de feature falharam"
        Write-Host $testOutput
        exit 1
    }
} catch {
    Write-Error "✗ Erro nos testes de feature: $_"
    exit 1
}

# Cobertura de código (opcional)
if ($Coverage) {
    Write-Info "📊 Gerando relatório de cobertura..."
    try {
        php ./vendor/bin/phpunit --coverage-html coverage-report --coverage-clover coverage.xml --coverage-text
        Write-Success "✓ Relatório de cobertura gerado em coverage-report/"
    } catch {
        Write-Warning "⚠ Erro ao gerar cobertura: $_"
    }
}

# Auditoria de segurança
Write-Info "🔒 Executando auditoria de segurança..."
try {
    $auditOutput = composer audit --format=plain 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Success "✓ Auditoria de segurança passou"
        if ($Verbose) { Write-Host $auditOutput }
    } else {
        Write-Warning "⚠ Vulnerabilidades encontradas"
        Write-Host $auditOutput
    }
} catch {
    Write-Warning "⚠ Erro na auditoria: $_"
}

# Resumo final
Write-Host ""
Write-Success "🎉 Pipeline de CI concluído com sucesso!"
Write-Host ""
Write-Info "📋 Resumo:"
Write-Host "   ✓ Análise estática (PHPStan)"
Write-Host "   ✓ Formatação de código (PHP CS Fixer)"
Write-Host "   ✓ Testes unitários"
Write-Host "   ✓ Testes de feature"
if ($Coverage) { Write-Host "   ✓ Relatório de cobertura" }
Write-Host "   ✓ Auditoria de segurança"
Write-Host ""
```

#### test.ps1
```powershell
#!/usr/bin/env pwsh

# Script simplificado para executar apenas testes

param(
    [string]$Suite = "all",
    [switch]$Coverage,
    [switch]$Verbose
)

$ErrorActionPreference = "Stop"

Write-Host "🧪 Executando testes - NFCeBox" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan

# Preparar ambiente
if (!(Test-Path ".env.testing")) {
    Copy-Item ".env.example" ".env.testing"
    (Get-Content ".env.testing") -replace "APP_ENV=local", "APP_ENV=testing" | Set-Content ".env.testing"
    (Get-Content ".env.testing") -replace "DB_CONNECTION=mysql", "DB_CONNECTION=sqlite" | Set-Content ".env.testing"
    (Get-Content ".env.testing") -replace "DB_DATABASE=nfcebox", "DB_DATABASE=:memory:" | Set-Content ".env.testing"
}

# Construir comando
$command = "php ./vendor/bin/phpunit"

switch ($Suite.ToLower()) {
    "unit" { $command += " --testsuite=Unit" }
    "feature" { $command += " --testsuite=Feature" }
    "all" { }
    default { $command += " --testsuite=$Suite" }
}

if ($Coverage) {
    $command += " --coverage-html coverage-report --coverage-clover coverage.xml"
} else {
    $command += " --no-coverage"
}

if ($Verbose) {
    $command += " --verbose"
}

# Executar testes
try {
    Invoke-Expression $command
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Testes concluídos com sucesso!" -ForegroundColor Green
    } else {
        Write-Host "❌ Alguns testes falharam!" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "❌ Erro ao executar testes: $_" -ForegroundColor Red
    exit 1
}
```

## 🧪 Testes Automatizados

### Estrutura de Testes

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegistrationTest.php
│   ├── Sales/
│   │   ├── SaleControllerTest.php
│   │   └── SaleCreationTest.php
│   ├── Products/
│   │   └── ProductControllerTest.php
│   └── Reports/
│       └── ReportGenerationTest.php
├── Unit/
│   ├── Models/
│   │   ├── SaleTest.php
│   │   ├── ProductTest.php
│   │   └── CustomerTest.php
│   ├── Services/
│   │   ├── NfceBuilderServiceTest.php
│   │   ├── XmlBuilderServiceTest.php
│   │   └── SefazClientServiceTest.php
│   └── Helpers/
│       └── FormattersTest.php
├── TestCase.php
└── CreatesApplication.php
```

### Configuração Base de Testes

#### tests/TestCase.php
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar ambiente de teste
        config(['app.env' => 'testing']);
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        
        // Executar migrations
        Artisan::call('migrate:fresh');
        
        // Seed básico para testes
        $this->seed(\Database\Seeders\TestScenarioSeeder::class);
    }
    
    protected function tearDown(): void
    {
        // Limpar arquivos temporários
        $this->cleanupTempFiles();
        
        parent::tearDown();
    }
    
    protected function cleanupTempFiles(): void
    {
        $tempDirs = [
            storage_path('app/temp'),
            storage_path('app/xml'),
            storage_path('app/pdf'),
        ];
        
        foreach ($tempDirs as $dir) {
            if (is_dir($dir)) {
                array_map('unlink', glob("$dir/*"));
            }
        }
    }
    
    protected function createAuthenticatedUser($role = 'operator')
    {
        $user = \App\Models\User::factory()->create(['role' => $role]);
        $this->actingAs($user);
        
        return $user;
    }
}
```

### Exemplo de Teste de Feature

#### tests/Feature/Sales/SaleCreationTest.php
```php
<?php

namespace Tests\Feature\Sales;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaleCreationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_authenticated_user_can_create_sale()
    {
        // Arrange
        $user = $this->createAuthenticatedUser('operator');
        $product = Product::factory()->create(['stock' => 10, 'price' => 25.50]);
        $customer = Customer::factory()->create();
        
        $saleData = [
            'customer_id' => $customer->id,
            'payment_method' => 'credit_card',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 25.50
                ]
            ]
        ];
        
        // Act
        $response = $this->postJson('/api/sales', $saleData);
        
        // Assert
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'customer_id',
                        'total',
                        'status',
                        'items'
                    ]
                ]);
        
        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total' => 51.00,
            'status' => 'completed'
        ]);
        
        // Verificar redução do estoque
        $product->refresh();
        $this->assertEquals(8, $product->stock);
    }
    
    public function test_sale_creation_fails_with_insufficient_stock()
    {
        // Arrange
        $this->createAuthenticatedUser('operator');
        $product = Product::factory()->create(['stock' => 1]);
        $customer = Customer::factory()->create();
        
        $saleData = [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5, // Mais que o estoque disponível
                    'unit_price' => 10.00
                ]
            ]
        ];
        
        // Act
        $response = $this->postJson('/api/sales', $saleData);
        
        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.quantity']);
        
        $this->assertDatabaseMissing('sales', [
            'customer_id' => $customer->id
        ]);
        
        // Verificar que o estoque não foi alterado
        $product->refresh();
        $this->assertEquals(1, $product->stock);
    }
}
```

### Exemplo de Teste Unitário

#### tests/Unit/Services/NfceBuilderServiceTest.php
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\NfceBuilderService;
use App\Models\Sale;
use App\Models\CompanyConfig;
use Mockery;

class NfceBuilderServiceTest extends TestCase
{
    protected NfceBuilderService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NfceBuilderService::class);
    }
    
    public function test_can_build_nfce_xml_structure()
    {
        // Arrange
        $sale = Sale::factory()->withItems(2)->create();
        $company = CompanyConfig::factory()->create();
        
        // Act
        $xml = $this->service->buildXml($sale);
        
        // Assert
        $this->assertIsString($xml);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<NFCe>', $xml);
        $this->assertStringContainsString('<infNFe>', $xml);
        $this->assertStringContainsString($company->cnpj, $xml);
        $this->assertStringContainsString($sale->total, $xml);
    }
    
    public function test_xml_contains_all_required_fields()
    {
        // Arrange
        $sale = Sale::factory()->withItems(1)->create();
        
        // Act
        $xml = $this->service->buildXml($sale);
        $xmlObject = simplexml_load_string($xml);
        
        // Assert - Verificar campos obrigatórios
        $this->assertNotNull($xmlObject->infNFe->ide->cNF);
        $this->assertNotNull($xmlObject->infNFe->ide->natOp);
        $this->assertNotNull($xmlObject->infNFe->ide->mod);
        $this->assertNotNull($xmlObject->infNFe->ide->serie);
        $this->assertNotNull($xmlObject->infNFe->ide->nNF);
        $this->assertNotNull($xmlObject->infNFe->ide->dhEmi);
        $this->assertNotNull($xmlObject->infNFe->ide->tpNF);
        $this->assertNotNull($xmlObject->infNFe->ide->idDest);
        $this->assertNotNull($xmlObject->infNFe->ide->cMunFG);
        $this->assertNotNull($xmlObject->infNFe->ide->tpImp);
        $this->assertNotNull($xmlObject->infNFe->ide->tpEmis);
        $this->assertNotNull($xmlObject->infNFe->ide->tpAmb);
    }
    
    public function test_throws_exception_for_invalid_sale()
    {
        // Arrange
        $sale = Sale::factory()->create(['total' => 0]);
        
        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Venda inválida para geração de NFCe');
        
        $this->service->buildXml($sale);
    }
}
```

## 📊 Cobertura de Código

### Configuração do Xdebug

#### php.ini (desenvolvimento)
```ini
[xdebug]
zend_extension=xdebug
xdebug.mode=coverage,debug
xdebug.start_with_request=yes
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.log_level=0
```

### Relatórios de Cobertura

#### Gerar Relatório HTML
```bash
# Relatório completo
php ./vendor/bin/phpunit --coverage-html coverage-report

# Apenas texto
php ./vendor/bin/phpunit --coverage-text

# XML para integração
php ./vendor/bin/phpunit --coverage-clover coverage.xml
```

#### Script de Análise de Cobertura
```powershell
# coverage-analysis.ps1

param(
    [int]$MinCoverage = 80
)

# Executar testes com cobertura
php ./vendor/bin/phpunit --coverage-clover coverage.xml --coverage-text > coverage.txt

# Extrair percentual de cobertura
$coverageText = Get-Content coverage.txt
$coverageLine = $coverageText | Select-String "Lines:\s+(\d+\.\d+)%"

if ($coverageLine) {
    $coverage = [float]$coverageLine.Matches[0].Groups[1].Value
    
    Write-Host "📊 Cobertura de código: $coverage%" -ForegroundColor Cyan
    
    if ($coverage -ge $MinCoverage) {
        Write-Host "✅ Cobertura adequada (>= $MinCoverage%)" -ForegroundColor Green
        exit 0
    } else {
        Write-Host "❌ Cobertura insuficiente (< $MinCoverage%)" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "⚠️ Não foi possível determinar a cobertura" -ForegroundColor Yellow
    exit 1
}
```

## 🔗 Hooks do Git

### Pre-commit Hook

#### .git/hooks/pre-commit
```bash
#!/bin/sh

# Pre-commit hook para NFCeBox
# Executa verificações básicas antes do commit

echo "🔍 Executando verificações pre-commit..."

# Verificar se há arquivos PHP modificados
php_files=$(git diff --cached --name-only --diff-filter=ACM | grep '\.php$')

if [ -z "$php_files" ]; then
    echo "✅ Nenhum arquivo PHP modificado"
    exit 0
fi

echo "📝 Verificando formatação de código..."
# Verificar formatação apenas dos arquivos modificados
for file in $php_files; do
    if [ -f "$file" ]; then
        ./vendor/bin/php-cs-fixer fix "$file" --dry-run --diff > /dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo "❌ Problemas de formatação em: $file"
            echo "Execute: ./vendor/bin/php-cs-fixer fix $file"
            exit 1
        fi
    fi
done

echo "🔍 Executando análise estática..."
# Análise estática apenas dos arquivos modificados
for file in $php_files; do
    if [ -f "$file" ]; then
        ./vendor/bin/phpstan analyse "$file" --no-progress > /dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo "❌ Problemas na análise estática em: $file"
            echo "Execute: ./vendor/bin/phpstan analyse $file"
            exit 1
        fi
    fi
done

echo "🧪 Executando testes rápidos..."
# Executar apenas testes unitários (mais rápidos)
./vendor/bin/phpunit --testsuite=Unit --no-coverage > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "❌ Testes unitários falharam"
    echo "Execute: composer test-unit"
    exit 1
fi

echo "✅ Todas as verificações passaram!"
exit 0
```

### Pre-push Hook

#### .git/hooks/pre-push
```bash
#!/bin/sh

# Pre-push hook para NFCeBox
# Executa pipeline completo antes do push

echo "🚀 Executando pipeline completo antes do push..."

# Executar pipeline de CI
if command -v pwsh > /dev/null 2>&1; then
    pwsh -File ci.ps1
else
    composer ci
fi

if [ $? -ne 0 ]; then
    echo "❌ Pipeline falhou. Push cancelado."
    exit 1
fi

echo "✅ Pipeline passou. Prosseguindo com push..."
exit 0
```

### Instalação dos Hooks

#### install-hooks.ps1
```powershell
#!/usr/bin/env pwsh

# Script para instalar hooks do Git

Write-Host "🔗 Instalando hooks do Git..." -ForegroundColor Cyan

# Verificar se estamos em um repositório Git
if (!(Test-Path ".git")) {
    Write-Error "❌ Não é um repositório Git"
    exit 1
}

# Criar diretório de hooks se não existir
if (!(Test-Path ".git/hooks")) {
    New-Item -ItemType Directory -Path ".git/hooks" -Force
}

# Copiar hooks
$hooks = @(
    "pre-commit",
    "pre-push"
)

foreach ($hook in $hooks) {
    $sourcePath = "scripts/git-hooks/$hook"
    $targetPath = ".git/hooks/$hook"
    
    if (Test-Path $sourcePath) {
        Copy-Item $sourcePath $targetPath -Force
        
        # Tornar executável (no Linux/Mac)
        if ($IsLinux -or $IsMacOS) {
            chmod +x $targetPath
        }
        
        Write-Host "✅ Hook $hook instalado" -ForegroundColor Green
    } else {
        Write-Warning "⚠️ Hook $hook não encontrado em $sourcePath"
    }
}

Write-Host "🎉 Hooks instalados com sucesso!" -ForegroundColor Green
Write-Host "Os hooks serão executados automaticamente nos commits e pushes." -ForegroundColor Yellow
```

## 🔧 Troubleshooting

### Problemas Comuns

#### 1. Testes Falhando

**Sintomas:**
- Testes passam localmente mas falham no CI
- Erros de conexão com banco
- Timeouts em testes

**Soluções:**
```bash
# Limpar cache de configuração
php artisan config:clear --env=testing
php artisan cache:clear --env=testing

# Verificar configuração de teste
php artisan config:show database --env=testing

# Executar migrations manualmente
php artisan migrate:fresh --env=testing
```

#### 2. Cobertura de Código Não Funciona

**Verificar Xdebug:**
```bash
# Verificar se Xdebug está instalado
php -m | grep xdebug

# Verificar configuração
php -i | grep xdebug.mode

# Instalar Xdebug (se necessário)
# Windows com XAMPP: já incluído
# Linux: sudo apt-get install php-xdebug
# Mac: brew install php@8.1-xdebug
```

#### 3. PHPStan Muito Lento

**Otimizações:**
```bash
# Usar cache
mkdir -p storage/phpstan

# Aumentar memória
php -d memory_limit=2G ./vendor/bin/phpstan analyse

# Analisar apenas arquivos modificados
git diff --name-only | grep '\.php$' | xargs ./vendor/bin/phpstan analyse
```

#### 4. PHP CS Fixer Conflitos

**Resolver conflitos:**
```bash
# Ver diferenças
./vendor/bin/php-cs-fixer fix --dry-run --diff

# Aplicar correções
./vendor/bin/php-cs-fixer fix

# Ignorar arquivos específicos
echo "storage/" >> .php-cs-fixer.ignore
echo "bootstrap/cache/" >> .php-cs-fixer.ignore
```

### Scripts de Diagnóstico

#### diagnose.ps1
```powershell
#!/usr/bin/env pwsh

# Script de diagnóstico do ambiente de CI

Write-Host "🔍 Diagnóstico do Ambiente de CI" -ForegroundColor Magenta
Write-Host "================================" -ForegroundColor Magenta

# Verificar PHP
Write-Host "\n📋 Versão do PHP:" -ForegroundColor Cyan
php --version

# Verificar extensões
Write-Host "\n🔌 Extensões PHP importantes:" -ForegroundColor Cyan
$extensions = @('xdebug', 'sqlite3', 'mbstring', 'xml', 'curl', 'zip')
foreach ($ext in $extensions) {
    $result = php -m | Select-String $ext
    if ($result) {
        Write-Host "  ✅ $ext" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $ext" -ForegroundColor Red
    }
}

# Verificar Composer
Write-Host "\n📦 Composer:" -ForegroundColor Cyan
composer --version

# Verificar dependências
Write-Host "\n🔍 Dependências de desenvolvimento:" -ForegroundColor Cyan
$devDeps = @('phpunit/phpunit', 'phpstan/phpstan', 'friendsofphp/php-cs-fixer')
foreach ($dep in $devDeps) {
    if (Test-Path "vendor/$dep") {
        Write-Host "  ✅ $dep" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $dep" -ForegroundColor Red
    }
}

# Verificar configurações
Write-Host "\n⚙️ Configurações:" -ForegroundColor Cyan
Write-Host "  phpunit.xml: $(if (Test-Path 'phpunit.xml') { '✅' } else { '❌' })"
Write-Host "  phpstan.neon: $(if (Test-Path 'phpstan.neon') { '✅' } else { '❌' })"
Write-Host "  .php-cs-fixer.php: $(if (Test-Path '.php-cs-fixer.php') { '✅' } else { '❌' })"
Write-Host "  .env.testing: $(if (Test-Path '.env.testing') { '✅' } else { '❌' })"

# Verificar permissões
Write-Host "\n📁 Diretórios:" -ForegroundColor Cyan
$dirs = @('storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views')
foreach ($dir in $dirs) {
    if (Test-Path $dir) {
        Write-Host "  ✅ $dir" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $dir (criando...)" -ForegroundColor Yellow
        New-Item -ItemType Directory -Path $dir -Force -ErrorAction SilentlyContinue
    }
}

Write-Host "\n🎉 Diagnóstico concluído!" -ForegroundColor Green
```

## 📚 Referências

### Documentação
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [PHP CS Fixer Documentation](https://cs.symfony.com/)
- [Laravel Testing](https://laravel.com/docs/testing)

### Ferramentas Adicionais
- [Pest PHP](https://pestphp.com/) - Alternativa moderna ao PHPUnit
- [Larastan](https://github.com/nunomaduro/larastan) - PHPStan para Laravel
- [PHP Insights](https://phpinsights.com/) - Análise de qualidade de código
- [Rector](https://getrector.org/) - Refatoração automática de código

### Integração com IDEs
- **VS Code:** PHP Intelephense, PHPUnit Test Explorer
- **PhpStorm:** Suporte nativo para PHPUnit, PHPStan e PHP CS Fixer
- **Sublime Text:** LSP-phpstan, SublimeLinter-phpcs