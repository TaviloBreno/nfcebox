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

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Product $product;

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
    }

    /**
     * Testa a listagem de vendas
     */
    public function test_sales_index_page_loads(): void
    {
        // Cria uma venda para teste
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('sales.index'));

        $response->assertStatus(200)
            ->assertViewIs('sales.index')
            ->assertViewHas('sales')
            ->assertSee('Vendas')
            ->assertSee('000001')
            ->assertSee('Cliente Teste');
    }

    /**
     * Testa a criação de uma nova venda
     */
    public function test_create_new_sale(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('sales.create'));

        $response->assertStatus(200)
            ->assertViewIs('sales.create')
            ->assertViewHas('customers')
            ->assertViewHas('products')
            ->assertSee('Nova Venda');
    }

    /**
     * Testa o armazenamento de uma nova venda
     */
    public function test_store_new_sale(): void
    {
        $saleData = [
            'customer_id' => $this->customer->id,
            'payment_method' => 'dinheiro',
            'discount' => 0.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2.000,
                    'unit_price' => 10.50
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('sales.store'), $saleData);

        $response->assertStatus(302);

        // Verifica se a venda foi criada
        $this->assertDatabaseHas('sales', [
            'customer_id' => $this->customer->id,
            'payment_method' => 'dinheiro',
            'subtotal' => 21.00,
            'total' => 21.00,
            'status' => 'draft'
        ]);

        // Verifica se o item foi criado
        $sale = Sale::where('customer_id', $this->customer->id)->first();
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 2.000,
            'unit_price' => 10.50,
            'total' => 21.00
        ]);

        // Verifica se o estoque foi reduzido
        $this->product->refresh();
        $this->assertEquals(98.000, $this->product->stock_qty);
    }

    /**
     * Testa a validação na criação de venda
     */
    public function test_store_sale_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('sales.store'), []);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'customer_id',
                'payment_method',
                'items'
            ]);
    }

    /**
     * Testa a validação de estoque insuficiente
     */
    public function test_store_sale_insufficient_stock(): void
    {
        $saleData = [
            'customer_id' => $this->customer->id,
            'payment_method' => 'dinheiro',
            'discount' => 0.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 150.000, // Mais que o estoque disponível
                    'unit_price' => 10.50
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('sales.store'), $saleData);

        $response->assertStatus(302)
            ->assertSessionHasErrors();

        // Verifica que a venda não foi criada
        $this->assertDatabaseMissing('sales', [
            'customer_id' => $this->customer->id
        ]);

        // Verifica que o estoque não foi alterado
        $this->product->refresh();
        $this->assertEquals(100.000, $this->product->stock_qty);
    }

    /**
     * Testa a visualização de uma venda
     */
    public function test_show_sale(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'draft'
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 2.000,
            'unit_price' => 10.50,
            'total' => 21.00
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('sales.show', $sale));

        $response->assertStatus(200)
            ->assertViewIs('sales.show')
            ->assertViewHas('sale')
            ->assertSee('000001')
            ->assertSee('Cliente Teste')
            ->assertSee('Produto Teste')
            ->assertSee('R$ 21,00');
    }

    /**
     * Testa a edição de uma venda
     */
    public function test_edit_sale(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('sales.edit', $sale));

        $response->assertStatus(200)
            ->assertViewIs('sales.edit')
            ->assertViewHas('sale')
            ->assertViewHas('customers')
            ->assertViewHas('products')
            ->assertSee('Editar Venda');
    }

    /**
     * Testa a atualização de uma venda
     */
    public function test_update_sale(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'draft'
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 2.000,
            'unit_price' => 10.50,
            'total' => 21.00
        ]);

        $updateData = [
            'customer_id' => $this->customer->id,
            'payment_method' => 'cartao_credito',
            'discount' => 1.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 3.000,
                    'unit_price' => 10.50
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->put(route('sales.update', $sale), $updateData);

        $response->assertStatus(302);

        // Verifica se a venda foi atualizada
        $sale->refresh();
        $this->assertEquals('cartao_credito', $sale->payment_method);
        $this->assertEquals(1.00, $sale->discount);
        $this->assertEquals(31.50, $sale->subtotal);
        $this->assertEquals(30.50, $sale->total);

        // Verifica se o item foi atualizado
        $saleItem = $sale->saleItems->first();
        $this->assertEquals(3.000, $saleItem->qty);
        $this->assertEquals(31.50, $saleItem->total);
    }

    /**
     * Testa a exclusão de uma venda
     */
    public function test_delete_sale(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'draft'
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 2.000,
            'unit_price' => 10.50,
            'total' => 21.00
        ]);

        // Reduz o estoque para simular a venda
        $this->product->decrement('stock_qty', 2.000);

        $response = $this->actingAs($this->user)
            ->delete(route('sales.destroy', $sale));

        $response->assertStatus(302);

        // Verifica se a venda foi excluída
        $this->assertDatabaseMissing('sales', [
            'id' => $sale->id
        ]);

        // Verifica se os itens foram excluídos
        $this->assertDatabaseMissing('sale_items', [
            'sale_id' => $sale->id
        ]);

        // Verifica se o estoque foi restaurado
        $this->product->refresh();
        $this->assertEquals(100.000, $this->product->stock_qty);
    }

    /**
     * Testa a finalização de uma venda
     */
    public function test_finalize_sale(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('sales.finalize', $sale));

        $response->assertStatus(302);

        // Verifica se o status foi alterado
        $sale->refresh();
        $this->assertEquals('completed', $sale->status);
    }

    /**
     * Testa o cancelamento de uma venda
     */
    public function test_cancel_sale(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'completed'
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 2.000,
            'unit_price' => 10.50,
            'total' => 21.00
        ]);

        // Reduz o estoque para simular a venda finalizada
        $this->product->decrement('stock_qty', 2.000);

        $response = $this->actingAs($this->user)
            ->patch(route('sales.cancel', $sale));

        $response->assertStatus(302);

        // Verifica se o status foi alterado
        $sale->refresh();
        $this->assertEquals('cancelled', $sale->status);

        // Verifica se o estoque foi restaurado
        $this->product->refresh();
        $this->assertEquals(100.000, $this->product->stock_qty);
    }

    /**
     * Testa que não é possível cancelar venda já cancelada
     */
    public function test_cannot_cancel_already_cancelled_sale(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'cancelled'
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('sales.cancel', $sale));

        $response->assertStatus(302)
            ->assertSessionHasErrors();
    }

    /**
     * Testa que não é possível finalizar venda já finalizada
     */
    public function test_cannot_finalize_already_completed_sale(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('sales.finalize', $sale));

        $response->assertStatus(302)
            ->assertSessionHasErrors();
    }

    /**
     * Testa o acesso negado para usuários não autorizados
     */
    public function test_sales_require_authentication(): void
    {
        $response = $this->get(route('sales.index'));
        
        $response->assertRedirect(route('login'));
    }

    /**
     * Testa a busca de vendas
     */
    public function test_search_sales(): void
    {
        $sale1 = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'completed'
        ]);

        $sale2 = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000002',
            'subtotal' => 15.75,
            'discount' => 0.00,
            'total' => 15.75,
            'payment_method' => 'cartao_credito',
            'status' => 'draft'
        ]);

        // Busca por número da venda
        $response = $this->actingAs($this->user)
            ->get(route('sales.index', ['search' => '000001']));

        $response->assertStatus(200)
            ->assertSee('000001')
            ->assertDontSee('000002');

        // Busca por nome do cliente
        $response = $this->actingAs($this->user)
            ->get(route('sales.index', ['search' => 'Cliente Teste']));

        $response->assertStatus(200)
            ->assertSee('000001')
            ->assertSee('000002');
    }

    /**
     * Testa o filtro por status
     */
    public function test_filter_sales_by_status(): void
    {
        $sale1 = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 21.00,
            'discount' => 0.00,
            'total' => 21.00,
            'payment_method' => 'dinheiro',
            'status' => 'completed'
        ]);

        $sale2 = Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000002',
            'subtotal' => 15.75,
            'discount' => 0.00,
            'total' => 15.75,
            'payment_method' => 'cartao_credito',
            'status' => 'draft'
        ]);

        // Filtra por status 'completed'
        $response = $this->actingAs($this->user)
            ->get(route('sales.index', ['status' => 'completed']));

        $response->assertStatus(200)
            ->assertSee('000001')
            ->assertDontSee('000002');

        // Filtra por status 'draft'
        $response = $this->actingAs($this->user)
            ->get(route('sales.index', ['status' => 'draft']));

        $response->assertStatus(200)
            ->assertSee('000002')
            ->assertDontSee('000001');
    }

    /**
     * Testa a paginação de vendas
     */
    public function test_sales_pagination(): void
    {
        // Cria 25 vendas para testar paginação
        for ($i = 1; $i <= 25; $i++) {
            Sale::create([
                'customer_id' => $this->customer->id,
                'sale_number' => sprintf('%06d', $i),
                'subtotal' => 10.00,
                'discount' => 0.00,
                'total' => 10.00,
                'payment_method' => 'dinheiro',
                'status' => 'draft'
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('sales.index'));

        $response->assertStatus(200)
            ->assertViewHas('sales')
            ->assertSee('Próxima'); // Link de paginação

        // Verifica se há paginação
        $sales = $response->viewData('sales');
        $this->assertEquals(20, $sales->perPage()); // Padrão do Laravel
    }
}