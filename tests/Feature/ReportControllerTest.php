<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Product $product;
    private Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTestData();
    }

    /**
     * Cria dados de teste
     */
    private function createTestData(): void
    {
        // Usuário de teste
        $this->user = User::factory()->create([
            'role' => 'admin'
        ]);

        // Cliente de teste
        $this->customer = Customer::create([
            'name' => 'Cliente Teste',
            'document' => '12345678900',
            'email' => 'cliente@teste.com.br',
            'phone' => '(11) 9876-5432'
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
            'status' => 'completed',
            'created_at' => now()->subDays(5)
        ]);

        // Item da venda
        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $this->product->id,
            'qty' => 2.000,
            'unit_price' => 10.50,
            'total' => 21.00
        ]);
    }

    /**
     * Testa o acesso à página de relatórios
     */
    public function test_reports_index_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.index'));

        $response->assertStatus(200)
            ->assertViewIs('reports.index')
            ->assertSee('Relatórios de Vendas')
            ->assertSee('Vendas por Período')
            ->assertSee('Vendas por Forma de Pagamento')
            ->assertSee('Produtos Mais Vendidos');
    }

    /**
     * Testa o relatório de vendas por período
     */
    public function test_sales_by_period_report(): void
    {
        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-period', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertViewIs('reports.sales-by-period')
            ->assertViewHas('sales')
            ->assertViewHas('summary')
            ->assertSee('Relatório de Vendas por Período')
            ->assertSee('Cliente Teste')
            ->assertSee('R$ 21,00');

        // Verifica dados do resumo
        $summary = $response->viewData('summary');
        $this->assertEquals(1, $summary['total_sales']);
        $this->assertEquals(21.00, $summary['total_amount']);
        $this->assertEquals(21.00, $summary['average_ticket']);
    }

    /**
     * Testa o relatório de vendas por período com datas inválidas
     */
    public function test_sales_by_period_with_invalid_dates(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-period', [
                'start_date' => 'invalid-date',
                'end_date' => now()->format('Y-m-d')
            ]));

        $response->assertStatus(302)
            ->assertSessionHasErrors(['start_date']);
    }

    /**
     * Testa o relatório de vendas por forma de pagamento
     */
    public function test_sales_by_payment_method_report(): void
    {
        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-payment', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertViewIs('reports.sales-by-payment')
            ->assertViewHas('paymentData')
            ->assertViewHas('summary')
            ->assertSee('Relatório de Vendas por Forma de Pagamento')
            ->assertSee('Dinheiro')
            ->assertSee('R$ 21,00');

        // Verifica dados do resumo
        $summary = $response->viewData('summary');
        $this->assertEquals(1, $summary['total_sales']);
        $this->assertEquals(21.00, $summary['total_amount']);

        // Verifica dados por forma de pagamento
        $paymentData = $response->viewData('paymentData');
        $this->assertCount(1, $paymentData);
        $this->assertEquals('dinheiro', $paymentData[0]->payment_method);
        $this->assertEquals(1, $paymentData[0]->total_sales);
        $this->assertEquals(21.00, $paymentData[0]->total_amount);
    }

    /**
     * Testa o relatório de vendas por cliente
     */
    public function test_sales_by_customer_report(): void
    {
        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-customer', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertViewIs('reports.sales-by-customer')
            ->assertViewHas('customerData')
            ->assertViewHas('summary')
            ->assertSee('Relatório de Vendas por Cliente')
            ->assertSee('Cliente Teste')
            ->assertSee('R$ 21,00');

        // Verifica dados por cliente
        $customerData = $response->viewData('customerData');
        $this->assertCount(1, $customerData);
        $this->assertEquals('Cliente Teste', $customerData[0]->customer_name);
        $this->assertEquals(1, $customerData[0]->total_sales);
        $this->assertEquals(21.00, $customerData[0]->total_amount);
    }

    /**
     * Testa o relatório de produtos mais vendidos
     */
    public function test_top_products_report(): void
    {
        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.top-products', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertViewIs('reports.top-products')
            ->assertViewHas('products')
            ->assertViewHas('summary')
            ->assertSee('Relatório de Produtos Mais Vendidos')
            ->assertSee('Produto Teste')
            ->assertSee('R$ 21,00');

        // Verifica dados dos produtos
        $products = $response->viewData('products');
        $this->assertCount(1, $products);
        $this->assertEquals('Produto Teste', $products[0]->product_name);
        $this->assertEquals(2.0, $products[0]->total_qty);
        $this->assertEquals(21.00, $products[0]->total_revenue);
    }

    /**
     * Testa a exportação CSV do relatório de vendas por período
     */
    public function test_export_sales_by_period_csv(): void
    {
        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-period.export-csv', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="vendas_por_periodo_' . now()->format('Y-m-d') . '.csv"');

        // Verifica conteúdo do CSV
        $content = $response->getContent();
        $this->assertStringContainsString('Data da Venda', $content);
        $this->assertStringContainsString('Cliente Teste', $content);
        $this->assertStringContainsString('21.00', $content);
    }

    /**
     * Testa a exportação PDF do relatório de vendas por período
     */
    public function test_export_sales_by_period_pdf(): void
    {
        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-period.export-pdf', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');

        // Verifica se o conteúdo é um PDF válido
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF', $content);
    }

    /**
     * Testa a exportação CSV do relatório de vendas por forma de pagamento
     */
    public function test_export_sales_by_payment_csv(): void
    {
        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-payment.export-csv', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="vendas_por_pagamento_' . now()->format('Y-m-d') . '.csv"');

        // Verifica conteúdo do CSV
        $content = $response->getContent();
        $this->assertStringContainsString('Forma de Pagamento', $content);
        $this->assertStringContainsString('Dinheiro', $content);
        $this->assertStringContainsString('21.00', $content);
    }

    /**
     * Testa a exportação CSV do relatório de produtos mais vendidos
     */
    public function test_export_top_products_csv(): void
    {
        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.top-products.export-csv', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="produtos_mais_vendidos_' . now()->format('Y-m-d') . '.csv"');

        // Verifica conteúdo do CSV
        $content = $response->getContent();
        $this->assertStringContainsString('Produto', $content);
        $this->assertStringContainsString('Produto Teste', $content);
        $this->assertStringContainsString('2.00', $content); // Quantidade
        $this->assertStringContainsString('21.00', $content); // Receita
    }

    /**
     * Testa o acesso negado para usuários não autorizados
     */
    public function test_reports_require_authentication(): void
    {
        $response = $this->get(route('reports.index'));
        
        $response->assertRedirect(route('login'));
    }

    /**
     * Testa o acesso negado para usuários sem permissão
     */
    public function test_reports_require_admin_role(): void
    {
        $regularUser = User::factory()->create([
            'role' => 'user'
        ]);

        $response = $this->actingAs($regularUser)
            ->get(route('reports.index'));

        $response->assertStatus(403);
    }

    /**
     * Testa relatório com período sem vendas
     */
    public function test_reports_with_no_sales(): void
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(10)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-period', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200)
            ->assertViewHas('sales')
            ->assertViewHas('summary');

        // Verifica que não há vendas
        $sales = $response->viewData('sales');
        $this->assertCount(0, $sales);

        // Verifica resumo zerado
        $summary = $response->viewData('summary');
        $this->assertEquals(0, $summary['total_sales']);
        $this->assertEquals(0, $summary['total_amount']);
        $this->assertEquals(0, $summary['average_ticket']);
    }

    /**
     * Testa relatório com múltiplas vendas e formas de pagamento
     */
    public function test_reports_with_multiple_sales(): void
    {
        // Cria segunda venda com cartão
        $sale2 = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000002',
            'subtotal' => 15.75,
            'discount' => 0.00,
            'total' => 15.75,
            'payment_method' => 'cartao_credito',
            'status' => 'completed',
            'created_at' => now()->subDays(3)
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $this->product->id,
            'qty' => 1.500,
            'unit_price' => 10.50,
            'total' => 15.75
        ]);

        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        // Testa relatório por forma de pagamento
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-payment', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200);

        // Verifica dados do resumo
        $summary = $response->viewData('summary');
        $this->assertEquals(2, $summary['total_sales']);
        $this->assertEquals(36.75, $summary['total_amount']);

        // Verifica dados por forma de pagamento
        $paymentData = $response->viewData('paymentData');
        $this->assertCount(2, $paymentData);

        // Verifica se ambas as formas de pagamento estão presentes
        $paymentMethods = collect($paymentData)->pluck('payment_method')->toArray();
        $this->assertContains('dinheiro', $paymentMethods);
        $this->assertContains('cartao_credito', $paymentMethods);
    }

    /**
     * Testa validação de parâmetros obrigatórios
     */
    public function test_reports_require_date_parameters(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-period'));

        $response->assertStatus(302)
            ->assertSessionHasErrors(['start_date', 'end_date']);
    }

    /**
     * Testa validação de data final maior que inicial
     */
    public function test_reports_validate_date_range(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-by-period', [
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->subDays(1)->format('Y-m-d')
            ]));

        $response->assertStatus(302)
            ->assertSessionHasErrors(['end_date']);
    }
}