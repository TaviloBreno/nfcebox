<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
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
            'cfop' => '5102',
            'category' => 'Eletrônicos'
        ]);
    }

    /**
     * Testa a listagem de produtos
     */
    public function test_products_index_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('products.index'));

        $response->assertStatus(200)
            ->assertViewIs('products.index')
            ->assertViewHas('products')
            ->assertSee('Produtos')
            ->assertSee('Produto Teste')
            ->assertSee('PROD001')
            ->assertSee('R$ 10,50');
    }

    /**
     * Testa a criação de um novo produto
     */
    public function test_create_new_product(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('products.create'));

        $response->assertStatus(200)
            ->assertViewIs('products.create')
            ->assertSee('Novo Produto')
            ->assertSee('Nome')
            ->assertSee('Código')
            ->assertSee('Preço');
    }

    /**
     * Testa o armazenamento de um novo produto
     */
    public function test_store_new_product(): void
    {
        Storage::fake('public');

        $productData = [
            'name' => 'Novo Produto',
            'description' => 'Descrição do novo produto',
            'code' => 'PROD002',
            'price' => 25.75,
            'stock_qty' => 50.000,
            'unit' => 'UN',
            'ncm' => '87654321',
            'cest' => '7654321',
            'cfop' => '5102',
            'category' => 'Informática',
            'image' => UploadedFile::fake()->image('produto.jpg')
        ];

        $response = $this->actingAs($this->user)
            ->post(route('products.store'), $productData);

        $response->assertStatus(302)
            ->assertRedirect(route('products.index'));

        // Verifica se o produto foi criado
        $this->assertDatabaseHas('products', [
            'name' => 'Novo Produto',
            'code' => 'PROD002',
            'price' => 25.75,
            'stock_qty' => 50.000,
            'category' => 'Informática'
        ]);

        // Verifica se a imagem foi salva
        $product = Product::where('code', 'PROD002')->first();
        if ($product->image) {
            Storage::disk('public')->assertExists($product->image);
        }
    }

    /**
     * Testa a validação na criação de produto
     */
    public function test_store_product_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('products.store'), []);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'name',
                'code',
                'price',
                'stock_qty',
                'unit',
                'ncm',
                'cfop'
            ]);
    }

    /**
     * Testa a validação de código único
     */
    public function test_store_product_unique_code(): void
    {
        $productData = [
            'name' => 'Produto Duplicado',
            'description' => 'Produto com código duplicado',
            'code' => 'PROD001', // Código já existe
            'price' => 15.00,
            'stock_qty' => 30.000,
            'unit' => 'UN',
            'ncm' => '11111111',
            'cfop' => '5102'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('products.store'), $productData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['code']);
    }

    /**
     * Testa a visualização de um produto
     */
    public function test_show_product(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('products.show', $this->product));

        $response->assertStatus(200)
            ->assertViewIs('products.show')
            ->assertViewHas('product')
            ->assertSee('Produto Teste')
            ->assertSee('PROD001')
            ->assertSee('R$ 10,50')
            ->assertSee('100,000 UN');
    }

    /**
     * Testa a edição de um produto
     */
    public function test_edit_product(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('products.edit', $this->product));

        $response->assertStatus(200)
            ->assertViewIs('products.edit')
            ->assertViewHas('product')
            ->assertSee('Editar Produto')
            ->assertSee('Produto Teste');
    }

    /**
     * Testa a atualização de um produto
     */
    public function test_update_product(): void
    {
        $updateData = [
            'name' => 'Produto Atualizado',
            'description' => 'Descrição atualizada',
            'code' => 'PROD001', // Mantém o mesmo código
            'price' => 12.75,
            'stock_qty' => 150.000,
            'unit' => 'UN',
            'ncm' => '12345678',
            'cest' => '1234567',
            'cfop' => '5102',
            'category' => 'Eletrônicos Atualizados'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('products.update', $this->product), $updateData);

        $response->assertStatus(302)
            ->assertRedirect(route('products.show', $this->product));

        // Verifica se o produto foi atualizado
        $this->product->refresh();
        $this->assertEquals('Produto Atualizado', $this->product->name);
        $this->assertEquals(12.75, $this->product->price);
        $this->assertEquals(150.000, $this->product->stock_qty);
        $this->assertEquals('Eletrônicos Atualizados', $this->product->category);
    }

    /**
     * Testa a atualização com nova imagem
     */
    public function test_update_product_with_new_image(): void
    {
        Storage::fake('public');

        $updateData = [
            'name' => 'Produto com Nova Imagem',
            'description' => 'Produto com imagem atualizada',
            'code' => 'PROD001',
            'price' => 15.00,
            'stock_qty' => 100.000,
            'unit' => 'UN',
            'ncm' => '12345678',
            'cfop' => '5102',
            'image' => UploadedFile::fake()->image('nova-imagem.jpg')
        ];

        $response = $this->actingAs($this->user)
            ->put(route('products.update', $this->product), $updateData);

        $response->assertStatus(302);

        // Verifica se a nova imagem foi salva
        $this->product->refresh();
        if ($this->product->image) {
            Storage::disk('public')->assertExists($this->product->image);
        }
    }

    /**
     * Testa a exclusão de um produto
     */
    public function test_delete_product(): void
    {
        $productId = $this->product->id;
        $imagePath = $this->product->image;

        $response = $this->actingAs($this->user)
            ->delete(route('products.destroy', $this->product));

        $response->assertStatus(302)
            ->assertRedirect(route('products.index'));

        // Verifica se o produto foi excluído
        $this->assertDatabaseMissing('products', [
            'id' => $productId
        ]);

        // Verifica se a imagem foi removida (se existia)
        if ($imagePath) {
            Storage::disk('public')->assertMissing($imagePath);
        }
    }

    /**
     * Testa que não é possível excluir produto com vendas
     */
    public function test_cannot_delete_product_with_sales(): void
    {
        // Simula uma venda com este produto
        $this->product->saleItems()->create([
            'sale_id' => 1, // ID fictício
            'qty' => 1.000,
            'unit_price' => 10.50,
            'total' => 10.50
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('products.destroy', $this->product));

        $response->assertStatus(302)
            ->assertSessionHasErrors();

        // Verifica que o produto não foi excluído
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id
        ]);
    }

    /**
     * Testa o acesso negado para usuários não autorizados
     */
    public function test_products_require_authentication(): void
    {
        $response = $this->get(route('products.index'));
        
        $response->assertRedirect(route('login'));
    }

    /**
     * Testa a busca de produtos
     */
    public function test_search_products(): void
    {
        $product2 = Product::create([
            'name' => 'Outro Produto',
            'description' => 'Outro produto para teste',
            'code' => 'PROD003',
            'price' => 20.00,
            'stock_qty' => 75.000,
            'unit' => 'UN',
            'ncm' => '11111111',
            'cfop' => '5102'
        ]);

        // Busca por nome
        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['search' => 'Produto Teste']));

        $response->assertStatus(200)
            ->assertSee('Produto Teste')
            ->assertDontSee('Outro Produto');

        // Busca por código
        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['search' => 'PROD003']));

        $response->assertStatus(200)
            ->assertSee('Outro Produto')
            ->assertDontSee('Produto Teste');
    }

    /**
     * Testa o filtro por categoria
     */
    public function test_filter_products_by_category(): void
    {
        $product2 = Product::create([
            'name' => 'Produto Informática',
            'description' => 'Produto da categoria informática',
            'code' => 'PROD004',
            'price' => 30.00,
            'stock_qty' => 25.000,
            'unit' => 'UN',
            'ncm' => '22222222',
            'cfop' => '5102',
            'category' => 'Informática'
        ]);

        // Filtra por categoria 'Eletrônicos'
        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['category' => 'Eletrônicos']));

        $response->assertStatus(200)
            ->assertSee('Produto Teste')
            ->assertDontSee('Produto Informática');

        // Filtra por categoria 'Informática'
        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['category' => 'Informática']));

        $response->assertStatus(200)
            ->assertSee('Produto Informática')
            ->assertDontSee('Produto Teste');
    }

    /**
     * Testa o filtro por estoque baixo
     */
    public function test_filter_products_by_low_stock(): void
    {
        $productLowStock = Product::create([
            'name' => 'Produto Estoque Baixo',
            'description' => 'Produto com estoque baixo',
            'code' => 'PROD005',
            'price' => 15.00,
            'stock_qty' => 5.000, // Estoque baixo
            'unit' => 'UN',
            'ncm' => '33333333',
            'cfop' => '5102'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['low_stock' => '1']));

        $response->assertStatus(200)
            ->assertSee('Produto Estoque Baixo')
            ->assertDontSee('Produto Teste'); // Este tem estoque alto
    }

    /**
     * Testa a ordenação de produtos
     */
    public function test_sort_products(): void
    {
        $product2 = Product::create([
            'name' => 'AAAA Primeiro Produto',
            'description' => 'Produto que deve aparecer primeiro',
            'code' => 'PROD006',
            'price' => 5.00,
            'stock_qty' => 200.000,
            'unit' => 'UN',
            'ncm' => '44444444',
            'cfop' => '5102'
        ]);

        // Ordena por nome (A-Z)
        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['sort' => 'name', 'direction' => 'asc']));

        $response->assertStatus(200);
        
        $products = $response->viewData('products');
        $this->assertEquals('AAAA Primeiro Produto', $products->first()->name);

        // Ordena por preço (maior para menor)
        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['sort' => 'price', 'direction' => 'desc']));

        $response->assertStatus(200);
        
        $products = $response->viewData('products');
        $this->assertEquals(10.50, $products->first()->price); // Produto Teste tem preço maior
    }

    /**
     * Testa a paginação de produtos
     */
    public function test_products_pagination(): void
    {
        // Cria 25 produtos para testar paginação
        for ($i = 1; $i <= 25; $i++) {
            Product::create([
                'name' => "Produto {$i}",
                'description' => "Descrição do produto {$i}",
                'code' => sprintf('PROD%03d', $i + 10),
                'price' => 10.00 + $i,
                'stock_qty' => 50.000,
                'unit' => 'UN',
                'ncm' => '12345678',
                'cfop' => '5102'
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('products.index'));

        $response->assertStatus(200)
            ->assertViewHas('products')
            ->assertSee('Próxima'); // Link de paginação

        // Verifica se há paginação
        $products = $response->viewData('products');
        $this->assertEquals(20, $products->perPage()); // Padrão do Laravel
    }

    /**
     * Testa a validação de tipos de arquivo para imagem
     */
    public function test_product_image_validation(): void
    {
        Storage::fake('public');

        $productData = [
            'name' => 'Produto com Arquivo Inválido',
            'description' => 'Teste de validação de imagem',
            'code' => 'PROD007',
            'price' => 20.00,
            'stock_qty' => 30.000,
            'unit' => 'UN',
            'ncm' => '12345678',
            'cfop' => '5102',
            'image' => UploadedFile::fake()->create('documento.pdf', 1000) // Arquivo não é imagem
        ];

        $response = $this->actingAs($this->user)
            ->post(route('products.store'), $productData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['image']);
    }

    /**
     * Testa a validação de tamanho máximo da imagem
     */
    public function test_product_image_size_validation(): void
    {
        Storage::fake('public');

        $productData = [
            'name' => 'Produto com Imagem Grande',
            'description' => 'Teste de validação de tamanho',
            'code' => 'PROD008',
            'price' => 25.00,
            'stock_qty' => 40.000,
            'unit' => 'UN',
            'ncm' => '12345678',
            'cfop' => '5102',
            'image' => UploadedFile::fake()->image('imagem-grande.jpg')->size(3000) // 3MB
        ];

        $response = $this->actingAs($this->user)
            ->post(route('products.store'), $productData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['image']);
    }
}