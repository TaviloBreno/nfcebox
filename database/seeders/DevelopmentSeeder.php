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
use Carbon\Carbon;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Cria dados realistas para ambiente de desenvolvimento.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Criar usuários com diferentes perfis
            $this->createUsers();
            
            // Criar clientes variados
            $this->createCustomers();
            
            // Criar produtos de diferentes categorias
            $this->createProducts();
            
            // Criar vendas dos últimos 6 meses
            $this->createSalesHistory();
        });

        $this->command->info('Seeds de desenvolvimento criados com sucesso!');
    }

    private function createUsers()
    {
        $users = [
            [
                'name' => 'Carlos Administrador',
                'email' => 'carlos.admin@nfcebox.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Ana Gerente',
                'email' => 'ana.gerente@nfcebox.com',
                'role' => 'manager',
            ],
            [
                'name' => 'Pedro Operador',
                'email' => 'pedro.operador@nfcebox.com',
                'role' => 'operator',
            ],
            [
                'name' => 'Lucia Vendedora',
                'email' => 'lucia.vendedora@nfcebox.com',
                'role' => 'operator',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ])
            );
        }
    }

    private function createCustomers()
    {
        $customers = [
            [
                'name' => 'Maria da Silva',
                'email' => 'maria.silva@email.com',
                'phone' => '11987654321',
                'document' => '12345678901',
                'document_type' => 'cpf',
                'address' => 'Rua das Flores, 123',
                'neighborhood' => 'Jardim Primavera',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567',
            ],
            [
                'name' => 'José Santos',
                'email' => 'jose.santos@email.com',
                'phone' => '11976543210',
                'document' => '98765432100',
                'document_type' => 'cpf',
                'address' => 'Av. Paulista, 456',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01310-100',
            ],
            [
                'name' => 'Empresa ABC Ltda',
                'email' => 'contato@empresaabc.com',
                'phone' => '1133334444',
                'document' => '11222333000144',
                'document_type' => 'cnpj',
                'address' => 'Rua Comercial, 789',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01001-000',
            ],
            [
                'name' => 'Comércio XYZ ME',
                'email' => 'vendas@comercioxyz.com',
                'phone' => '1155556666',
                'document' => '44555666000177',
                'document_type' => 'cnpj',
                'address' => 'Av. Industrial, 321',
                'neighborhood' => 'Vila Industrial',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '03000-000',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::firstOrCreate(
                ['document' => $customerData['document']],
                $customerData
            );
        }

        // Criar mais clientes aleatórios
        Customer::factory()->count(25)->create();
    }

    private function createProducts()
    {
        $products = [
            // Eletrônicos
            [
                'name' => 'Smartphone Samsung Galaxy',
                'code' => 'ELET001',
                'description' => 'Smartphone Android com 128GB',
                'price' => 899.99,
                'stock_quantity' => 15,
                'min_stock' => 5,
                'category' => 'Eletrônicos',
                'barcode' => '7891234567001',
                'ncm' => '85171231',
                'cfop' => '5102',
                'icms_rate' => 18.00,
            ],
            [
                'name' => 'Notebook Dell Inspiron',
                'code' => 'ELET002',
                'description' => 'Notebook Intel i5, 8GB RAM, 256GB SSD',
                'price' => 2499.99,
                'stock_quantity' => 8,
                'min_stock' => 2,
                'category' => 'Eletrônicos',
                'barcode' => '7891234567002',
                'ncm' => '84713012',
                'cfop' => '5102',
                'icms_rate' => 18.00,
            ],
            // Roupas
            [
                'name' => 'Camiseta Polo Masculina',
                'code' => 'ROUP001',
                'description' => 'Camiseta polo 100% algodão, tamanho M',
                'price' => 59.90,
                'stock_quantity' => 30,
                'min_stock' => 10,
                'category' => 'Roupas',
                'barcode' => '7891234567003',
                'ncm' => '61051000',
                'cfop' => '5102',
                'icms_rate' => 18.00,
            ],
            [
                'name' => 'Calça Jeans Feminina',
                'code' => 'ROUP002',
                'description' => 'Calça jeans skinny, tamanho 38',
                'price' => 89.90,
                'stock_quantity' => 25,
                'min_stock' => 8,
                'category' => 'Roupas',
                'barcode' => '7891234567004',
                'ncm' => '62034200',
                'cfop' => '5102',
                'icms_rate' => 18.00,
            ],
            // Casa e Decoração
            [
                'name' => 'Conjunto de Panelas Antiaderente',
                'code' => 'CASA001',
                'description' => 'Kit com 5 panelas antiaderente',
                'price' => 199.99,
                'stock_quantity' => 12,
                'min_stock' => 3,
                'category' => 'Casa e Decoração',
                'barcode' => '7891234567005',
                'ncm' => '73239300',
                'cfop' => '5102',
                'icms_rate' => 18.00,
            ],
            [
                'name' => 'Luminária LED de Mesa',
                'code' => 'CASA002',
                'description' => 'Luminária LED ajustável, 10W',
                'price' => 79.90,
                'stock_quantity' => 20,
                'min_stock' => 5,
                'category' => 'Casa e Decoração',
                'barcode' => '7891234567006',
                'ncm' => '94051000',
                'cfop' => '5102',
                'icms_rate' => 18.00,
            ],
            // Livros
            [
                'name' => 'Livro: Clean Code',
                'code' => 'LIVR001',
                'description' => 'Livro sobre programação limpa',
                'price' => 89.90,
                'stock_quantity' => 18,
                'min_stock' => 5,
                'category' => 'Livros',
                'barcode' => '7891234567007',
                'ncm' => '49019900',
                'cfop' => '5102',
                'icms_rate' => 0.00,
            ],
            [
                'name' => 'Livro: Design Patterns',
                'code' => 'LIVR002',
                'description' => 'Padrões de projeto em programação',
                'price' => 119.90,
                'stock_quantity' => 10,
                'min_stock' => 3,
                'category' => 'Livros',
                'barcode' => '7891234567008',
                'ncm' => '49019900',
                'cfop' => '5102',
                'icms_rate' => 0.00,
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['code' => $productData['code']],
                array_merge($productData, ['active' => true])
            );
        }

        // Criar mais produtos aleatórios
        Product::factory()->count(40)->create();
    }

    private function createSalesHistory()
    {
        $customers = Customer::all();
        $products = Product::all();
        $users = User::all();

        if ($customers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            return;
        }

        $paymentMethods = ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix'];
        $saleNumber = 1;

        // Criar vendas dos últimos 6 meses
        for ($month = 5; $month >= 0; $month--) {
            $startDate = now()->subMonths($month)->startOfMonth();
            $endDate = now()->subMonths($month)->endOfMonth();
            
            // Número de vendas varia por mês (mais vendas nos meses recentes)
            $salesCount = $month <= 2 ? rand(15, 25) : rand(8, 15);
            
            for ($i = 0; $i < $salesCount; $i++) {
                $saleDate = $startDate->copy()->addDays(rand(0, $endDate->diffInDays($startDate)));
                
                // 85% das vendas são finalizadas, 10% pendentes, 5% canceladas
                $statusRand = rand(1, 100);
                if ($statusRand <= 85) {
                    $status = 'finalizada';
                } elseif ($statusRand <= 95) {
                    $status = 'pendente';
                } else {
                    $status = 'cancelada';
                }

                $sale = Sale::create([
                    'customer_id' => $customers->random()->id,
                    'user_id' => $users->random()->id,
                    'sale_number' => 'VND-' . str_pad($saleNumber++, 6, '0', STR_PAD_LEFT),
                    'sale_date' => $saleDate,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => $status,
                    'total_amount' => 0,
                    'discount' => rand(0, 100),
                    'notes' => $status === 'cancelada' ? 'Venda cancelada - desenvolvimento' : 'Venda de desenvolvimento',
                    'canceled_at' => $status === 'cancelada' ? $saleDate->copy()->addHours(rand(1, 48)) : null,
                    'cancellation_reason' => $status === 'cancelada' ? 'Cancelamento de desenvolvimento' : null,
                ]);

                // Adicionar itens à venda
                $totalAmount = 0;
                $numItems = rand(1, 5);
                
                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 3);
                    $unitPrice = $product->price;
                    $subtotal = $quantity * $unitPrice;
                    $totalAmount += $subtotal;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);
                }

                $sale->update(['total_amount' => max(0, $totalAmount - $sale->discount)]);
            }
        }
    }
}