<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CompanyConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestScenarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Cria dados específicos para cenários de teste automatizado.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Usuários específicos para teste
            $adminUser = User::firstOrCreate(
                ['email' => 'admin.test@nfcebox.com'],
                [
                    'name' => 'Admin Test',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'role' => 'admin',
                ]
            );

            $operatorUser = User::firstOrCreate(
                ['email' => 'operator.test@nfcebox.com'],
                [
                    'name' => 'Operator Test',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'role' => 'operator',
                ]
            );

            // Clientes específicos para teste
            $customerPF = Customer::firstOrCreate(
                ['document' => '12345678901'],
                [
                    'name' => 'João Silva Teste',
                    'email' => 'joao.test@email.com',
                    'phone' => '11999999999',
                    'document_type' => 'cpf',
                    'address' => 'Rua Teste, 123',
                    'neighborhood' => 'Centro',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zip_code' => '01000-000',
                ]
            );

            $customerPJ = Customer::firstOrCreate(
                ['document' => '12345678000195'],
                [
                    'name' => 'Empresa Teste Ltda',
                    'email' => 'empresa.test@email.com',
                    'phone' => '1133333333',
                    'document_type' => 'cnpj',
                    'address' => 'Av. Teste, 456',
                    'neighborhood' => 'Comercial',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zip_code' => '01001-000',
                ]
            );

            // Produtos específicos para teste
            $product1 = Product::firstOrCreate(
                ['code' => 'TEST001'],
                [
                    'name' => 'Produto Teste 1',
                    'description' => 'Produto para testes automatizados',
                    'price' => 10.50,
                    'stock_quantity' => 100,
                    'min_stock' => 10,
                    'category' => 'Teste',
                    'barcode' => '7891234567890',
                    'ncm' => '12345678',
                    'cfop' => '5102',
                    'icms_rate' => 18.00,
                    'active' => true,
                ]
            );

            $product2 = Product::firstOrCreate(
                ['code' => 'TEST002'],
                [
                    'name' => 'Produto Teste 2',
                    'description' => 'Segundo produto para testes',
                    'price' => 25.75,
                    'stock_quantity' => 50,
                    'min_stock' => 5,
                    'category' => 'Teste',
                    'barcode' => '7891234567891',
                    'ncm' => '12345679',
                    'cfop' => '5102',
                    'icms_rate' => 18.00,
                    'active' => true,
                ]
            );

            $product3 = Product::firstOrCreate(
                ['code' => 'TEST003'],
                [
                    'name' => 'Produto Estoque Baixo',
                    'description' => 'Produto com estoque baixo para teste',
                    'price' => 15.00,
                    'stock_quantity' => 2,
                    'min_stock' => 10,
                    'category' => 'Teste',
                    'barcode' => '7891234567892',
                    'ncm' => '12345680',
                    'cfop' => '5102',
                    'icms_rate' => 18.00,
                    'active' => true,
                ]
            );

            // Configuração da empresa para teste
            CompanyConfig::firstOrCreate(
                ['id' => 1],
                [
                    'company_name' => 'Empresa Teste NFCe',
                    'trade_name' => 'Teste NFCe',
                    'cnpj' => '12.345.678/0001-95',
                    'ie' => '123456789',
                    'address' => 'Rua da Empresa, 789',
                    'neighborhood' => 'Centro',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zip_code' => '01002-000',
                    'phone' => '1144444444',
                    'email' => 'contato@empresateste.com',
                    'environment' => 'homologacao',
                    'certificate_path' => null,
                    'certificate_password' => null,
                    'csc_id' => '000001',
                    'csc_token' => 'token_teste_123456789',
                    'series_number' => 1,
                    'last_nfce_number' => 0,
                ]
            );

            // Vendas específicas para teste de relatórios
            $this->createTestSales($adminUser, $operatorUser, $customerPF, $customerPJ, $product1, $product2, $product3);
        });

        $this->command->info('Seeds de cenários de teste criados com sucesso!');
    }

    private function createTestSales($adminUser, $operatorUser, $customerPF, $customerPJ, $product1, $product2, $product3)
    {
        // Venda finalizada - mês atual
        $sale1 = Sale::create([
            'customer_id' => $customerPF->id,
            'user_id' => $adminUser->id,
            'sale_number' => 'TEST-000001',
            'sale_date' => now()->startOfMonth()->addDays(5),
            'payment_method' => 'dinheiro',
            'status' => 'finalizada',
            'total_amount' => 36.25,
            'discount' => 0,
            'notes' => 'Venda teste - dinheiro',
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 10.50,
            'subtotal' => 21.00,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 25.75,
            'subtotal' => 25.75,
        ]);

        // Venda finalizada - cartão de crédito
        $sale2 = Sale::create([
            'customer_id' => $customerPJ->id,
            'user_id' => $operatorUser->id,
            'sale_number' => 'TEST-000002',
            'sale_date' => now()->startOfMonth()->addDays(10),
            'payment_method' => 'cartao_credito',
            'status' => 'finalizada',
            'total_amount' => 45.00,
            'discount' => 5.00,
            'notes' => 'Venda teste - cartão crédito',
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $product3->id,
            'quantity' => 3,
            'unit_price' => 15.00,
            'subtotal' => 45.00,
        ]);

        // Venda pendente
        $sale3 = Sale::create([
            'customer_id' => $customerPF->id,
            'user_id' => $operatorUser->id,
            'sale_number' => 'TEST-000003',
            'sale_date' => now()->subDays(2),
            'payment_method' => 'pix',
            'status' => 'pendente',
            'total_amount' => 10.50,
            'discount' => 0,
            'notes' => 'Venda teste - pendente',
        ]);

        SaleItem::create([
            'sale_id' => $sale3->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'unit_price' => 10.50,
            'subtotal' => 10.50,
        ]);

        // Venda cancelada
        $sale4 = Sale::create([
            'customer_id' => $customerPJ->id,
            'user_id' => $adminUser->id,
            'sale_number' => 'TEST-000004',
            'sale_date' => now()->subDays(5),
            'payment_method' => 'cartao_debito',
            'status' => 'cancelada',
            'total_amount' => 25.75,
            'discount' => 0,
            'notes' => 'Venda teste - cancelada',
            'canceled_at' => now()->subDays(3),
            'cancellation_reason' => 'Teste de cancelamento',
        ]);

        SaleItem::create([
            'sale_id' => $sale4->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 25.75,
            'subtotal' => 25.75,
        ]);

        // Venda do mês anterior para teste de período
        $sale5 = Sale::create([
            'customer_id' => $customerPF->id,
            'user_id' => $adminUser->id,
            'sale_number' => 'TEST-000005',
            'sale_date' => now()->subMonth()->endOfMonth(),
            'payment_method' => 'dinheiro',
            'status' => 'finalizada',
            'total_amount' => 52.50,
            'discount' => 0,
            'notes' => 'Venda teste - mês anterior',
        ]);

        SaleItem::create([
            'sale_id' => $sale5->id,
            'product_id' => $product1->id,
            'quantity' => 5,
            'unit_price' => 10.50,
            'subtotal' => 52.50,
        ]);
    }
}