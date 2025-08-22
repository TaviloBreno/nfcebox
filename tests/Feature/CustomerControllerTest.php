<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

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
            'phone' => '(11) 9876-5432',
            'address' => json_encode([
                'street' => 'Rua Teste',
                'number' => '123',
                'complement' => 'Apto 45',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234567'
            ])
        ]);
    }

    /**
     * Testa a listagem de clientes
     */
    public function test_customers_index_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('customers.index'));

        $response->assertStatus(200)
            ->assertViewIs('customers.index')
            ->assertViewHas('customers')
            ->assertSee('Clientes')
            ->assertSee('Cliente Teste')
            ->assertSee('123.456.789-00')
            ->assertSee('cliente@teste.com.br');
    }

    /**
     * Testa a criação de um novo cliente
     */
    public function test_create_new_customer(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('customers.create'));

        $response->assertStatus(200)
            ->assertViewIs('customers.create')
            ->assertSee('Novo Cliente')
            ->assertSee('Nome')
            ->assertSee('CPF/CNPJ')
            ->assertSee('E-mail');
    }

    /**
     * Testa o armazenamento de um novo cliente pessoa física
     */
    public function test_store_new_customer_cpf(): void
    {
        $customerData = [
            'name' => 'João Silva',
            'document' => '98765432100',
            'email' => 'joao@teste.com.br',
            'phone' => '(11) 1234-5678',
            'address' => [
                'street' => 'Rua Nova',
                'number' => '456',
                'complement' => '',
                'neighborhood' => 'Vila Nova',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '12345678'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertStatus(302)
            ->assertRedirect(route('customers.index'));

        // Verifica se o cliente foi criado
        $this->assertDatabaseHas('customers', [
            'name' => 'João Silva',
            'document' => '98765432100',
            'email' => 'joao@teste.com.br',
            'phone' => '(11) 1234-5678'
        ]);

        // Verifica se o endereço foi salvo corretamente
        $customer = Customer::where('document', '98765432100')->first();
        $address = json_decode($customer->address, true);
        $this->assertEquals('Rua Nova', $address['street']);
        $this->assertEquals('456', $address['number']);
        $this->assertEquals('Vila Nova', $address['neighborhood']);
    }

    /**
     * Testa o armazenamento de um novo cliente pessoa jurídica
     */
    public function test_store_new_customer_cnpj(): void
    {
        $customerData = [
            'name' => 'Empresa Teste LTDA',
            'document' => '11222333000181',
            'email' => 'contato@empresa.com.br',
            'phone' => '(11) 3333-4444',
            'address' => [
                'street' => 'Av. Empresarial',
                'number' => '1000',
                'complement' => 'Sala 101',
                'neighborhood' => 'Centro Empresarial',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01000000'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertStatus(302);

        // Verifica se o cliente foi criado
        $this->assertDatabaseHas('customers', [
            'name' => 'Empresa Teste LTDA',
            'document' => '11222333000181',
            'email' => 'contato@empresa.com.br'
        ]);
    }

    /**
     * Testa a validação na criação de cliente
     */
    public function test_store_customer_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), []);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'name',
                'document',
                'email'
            ]);
    }

    /**
     * Testa a validação de CPF inválido
     */
    public function test_store_customer_invalid_cpf(): void
    {
        $customerData = [
            'name' => 'Cliente CPF Inválido',
            'document' => '11111111111', // CPF inválido
            'email' => 'invalido@teste.com.br',
            'phone' => '(11) 1111-1111'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['document']);
    }

    /**
     * Testa a validação de CNPJ inválido
     */
    public function test_store_customer_invalid_cnpj(): void
    {
        $customerData = [
            'name' => 'Empresa CNPJ Inválido',
            'document' => '11111111111111', // CNPJ inválido
            'email' => 'invalido@empresa.com.br',
            'phone' => '(11) 2222-2222'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['document']);
    }

    /**
     * Testa a validação de documento único
     */
    public function test_store_customer_unique_document(): void
    {
        $customerData = [
            'name' => 'Cliente Duplicado',
            'document' => '12345678900', // Documento já existe
            'email' => 'duplicado@teste.com.br',
            'phone' => '(11) 3333-3333'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['document']);
    }

    /**
     * Testa a validação de e-mail único
     */
    public function test_store_customer_unique_email(): void
    {
        $customerData = [
            'name' => 'Cliente Email Duplicado',
            'document' => '98765432100',
            'email' => 'cliente@teste.com.br', // E-mail já existe
            'phone' => '(11) 4444-4444'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    /**
     * Testa a visualização de um cliente
     */
    public function test_show_customer(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('customers.show', $this->customer));

        $response->assertStatus(200)
            ->assertViewIs('customers.show')
            ->assertViewHas('customer')
            ->assertSee('Cliente Teste')
            ->assertSee('123.456.789-00')
            ->assertSee('cliente@teste.com.br')
            ->assertSee('Rua Teste, 123');
    }

    /**
     * Testa a edição de um cliente
     */
    public function test_edit_customer(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('customers.edit', $this->customer));

        $response->assertStatus(200)
            ->assertViewIs('customers.edit')
            ->assertViewHas('customer')
            ->assertSee('Editar Cliente')
            ->assertSee('Cliente Teste');
    }

    /**
     * Testa a atualização de um cliente
     */
    public function test_update_customer(): void
    {
        $updateData = [
            'name' => 'Cliente Atualizado',
            'document' => '12345678900', // Mantém o mesmo documento
            'email' => 'atualizado@teste.com.br',
            'phone' => '(11) 9999-8888',
            'address' => [
                'street' => 'Rua Atualizada',
                'number' => '789',
                'complement' => 'Casa',
                'neighborhood' => 'Bairro Novo',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '87654321'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->put(route('customers.update', $this->customer), $updateData);

        $response->assertStatus(302)
            ->assertRedirect(route('customers.show', $this->customer));

        // Verifica se o cliente foi atualizado
        $this->customer->refresh();
        $this->assertEquals('Cliente Atualizado', $this->customer->name);
        $this->assertEquals('atualizado@teste.com.br', $this->customer->email);
        $this->assertEquals('(11) 9999-8888', $this->customer->phone);

        // Verifica se o endereço foi atualizado
        $address = json_decode($this->customer->address, true);
        $this->assertEquals('Rua Atualizada', $address['street']);
        $this->assertEquals('789', $address['number']);
        $this->assertEquals('Bairro Novo', $address['neighborhood']);
    }

    /**
     * Testa a exclusão de um cliente
     */
    public function test_delete_customer(): void
    {
        $customerId = $this->customer->id;

        $response = $this->actingAs($this->user)
            ->delete(route('customers.destroy', $this->customer));

        $response->assertStatus(302)
            ->assertRedirect(route('customers.index'));

        // Verifica se o cliente foi excluído
        $this->assertDatabaseMissing('customers', [
            'id' => $customerId
        ]);
    }

    /**
     * Testa que não é possível excluir cliente com vendas
     */
    public function test_cannot_delete_customer_with_sales(): void
    {
        // Cria uma venda para este cliente
        Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 100.00,
            'discount' => 0.00,
            'total' => 100.00,
            'payment_method' => 'dinheiro',
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('customers.destroy', $this->customer));

        $response->assertStatus(302)
            ->assertSessionHasErrors();

        // Verifica que o cliente não foi excluído
        $this->assertDatabaseHas('customers', [
            'id' => $this->customer->id
        ]);
    }

    /**
     * Testa o acesso negado para usuários não autorizados
     */
    public function test_customers_require_authentication(): void
    {
        $response = $this->get(route('customers.index'));
        
        $response->assertRedirect(route('login'));
    }

    /**
     * Testa a busca de clientes
     */
    public function test_search_customers(): void
    {
        $customer2 = Customer::create([
            'name' => 'Maria Santos',
            'document' => '98765432100',
            'email' => 'maria@teste.com.br',
            'phone' => '(11) 5555-6666'
        ]);

        // Busca por nome
        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['search' => 'Cliente Teste']));

        $response->assertStatus(200)
            ->assertSee('Cliente Teste')
            ->assertDontSee('Maria Santos');

        // Busca por documento
        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['search' => '987.654.321-00']));

        $response->assertStatus(200)
            ->assertSee('Maria Santos')
            ->assertDontSee('Cliente Teste');

        // Busca por e-mail
        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['search' => 'maria@teste.com.br']));

        $response->assertStatus(200)
            ->assertSee('Maria Santos')
            ->assertDontSee('Cliente Teste');
    }

    /**
     * Testa o filtro por tipo de documento
     */
    public function test_filter_customers_by_document_type(): void
    {
        // Cria cliente pessoa jurídica
        $customerPJ = Customer::create([
            'name' => 'Empresa Teste LTDA',
            'document' => '11222333000181',
            'email' => 'empresa@teste.com.br',
            'phone' => '(11) 7777-8888'
        ]);

        // Filtra por CPF (pessoa física)
        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['document_type' => 'cpf']));

        $response->assertStatus(200)
            ->assertSee('Cliente Teste')
            ->assertDontSee('Empresa Teste LTDA');

        // Filtra por CNPJ (pessoa jurídica)
        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['document_type' => 'cnpj']));

        $response->assertStatus(200)
            ->assertSee('Empresa Teste LTDA')
            ->assertDontSee('Cliente Teste');
    }

    /**
     * Testa a ordenação de clientes
     */
    public function test_sort_customers(): void
    {
        $customer2 = Customer::create([
            'name' => 'AAAA Primeiro Cliente',
            'document' => '11111111111',
            'email' => 'primeiro@teste.com.br',
            'phone' => '(11) 1111-1111'
        ]);

        // Ordena por nome (A-Z)
        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['sort' => 'name', 'direction' => 'asc']));

        $response->assertStatus(200);
        
        $customers = $response->viewData('customers');
        $this->assertEquals('AAAA Primeiro Cliente', $customers->first()->name);

        // Ordena por nome (Z-A)
        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['sort' => 'name', 'direction' => 'desc']));

        $response->assertStatus(200);
        
        $customers = $response->viewData('customers');
        $this->assertEquals('Cliente Teste', $customers->first()->name);
    }

    /**
     * Testa a paginação de clientes
     */
    public function test_customers_pagination(): void
    {
        // Cria 25 clientes para testar paginação
        for ($i = 1; $i <= 25; $i++) {
            Customer::create([
                'name' => "Cliente {$i}",
                'document' => sprintf('%011d', $i + 10000000000),
                'email' => "cliente{$i}@teste.com.br",
                'phone' => sprintf('(11) %04d-%04d', $i, $i)
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('customers.index'));

        $response->assertStatus(200)
            ->assertViewHas('customers')
            ->assertSee('Próxima'); // Link de paginação

        // Verifica se há paginação
        $customers = $response->viewData('customers');
        $this->assertEquals(20, $customers->perPage()); // Padrão do Laravel
    }

    /**
     * Testa a validação de formato de telefone
     */
    public function test_customer_phone_validation(): void
    {
        $customerData = [
            'name' => 'Cliente Telefone Inválido',
            'document' => '98765432100',
            'email' => 'telefone@teste.com.br',
            'phone' => '11999887766' // Formato inválido
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['phone']);
    }

    /**
     * Testa a validação de formato de CEP
     */
    public function test_customer_zip_code_validation(): void
    {
        $customerData = [
            'name' => 'Cliente CEP Inválido',
            'document' => '98765432100',
            'email' => 'cep@teste.com.br',
            'phone' => '(11) 9999-8888',
            'address' => [
                'street' => 'Rua Teste',
                'number' => '123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '1234567' // CEP inválido (7 dígitos)
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['address.zip_code']);
    }

    /**
     * Testa a exibição do histórico de vendas do cliente
     */
    public function test_customer_sales_history(): void
    {
        // Cria algumas vendas para o cliente
        Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000001',
            'subtotal' => 100.00,
            'discount' => 0.00,
            'total' => 100.00,
            'payment_method' => 'dinheiro',
            'status' => 'completed'
        ]);

        Sale::create([
            'customer_id' => $this->customer->id,
            'sale_number' => '000002',
            'subtotal' => 50.00,
            'discount' => 5.00,
            'total' => 45.00,
            'payment_method' => 'cartao_credito',
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('customers.show', $this->customer));

        $response->assertStatus(200)
            ->assertSee('Histórico de Vendas')
            ->assertSee('000001')
            ->assertSee('000002')
            ->assertSee('R$ 100,00')
            ->assertSee('R$ 45,00');
    }
}