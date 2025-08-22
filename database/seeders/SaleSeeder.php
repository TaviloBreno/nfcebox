<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $users = User::all();

        if ($customers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Certifique-se de que existem clientes, produtos e usuários antes de executar este seeder.');
            return;
        }

        DB::transaction(function () use ($customers, $products, $users) {
            // Criar vendas finalizadas (últimos 30 dias)
            for ($i = 0; $i < 15; $i++) {
                $sale = Sale::create([
                    'customer_id' => $customers->random()->id,
                    'user_id' => $users->random()->id,
                    'sale_number' => 'VND-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                    'sale_date' => now()->subDays(rand(0, 30)),
                    'payment_method' => collect(['dinheiro', 'cartao_credito', 'cartao_debito', 'pix'])->random(),
                    'status' => 'finalizada',
                    'total_amount' => 0, // Será calculado após adicionar itens
                    'discount' => rand(0, 50),
                    'notes' => 'Venda de teste - ' . fake()->sentence(),
                ]);

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

                $sale->update(['total_amount' => $totalAmount - $sale->discount]);
            }

            // Criar vendas pendentes
            for ($i = 0; $i < 5; $i++) {
                $sale = Sale::create([
                    'customer_id' => $customers->random()->id,
                    'user_id' => $users->random()->id,
                    'sale_number' => 'VND-' . str_pad($i + 16, 6, '0', STR_PAD_LEFT),
                    'sale_date' => now()->subDays(rand(0, 7)),
                    'payment_method' => collect(['dinheiro', 'cartao_credito', 'cartao_debito', 'pix'])->random(),
                    'status' => 'pendente',
                    'total_amount' => 0,
                    'discount' => rand(0, 20),
                    'notes' => 'Venda pendente - ' . fake()->sentence(),
                ]);

                $totalAmount = 0;
                $numItems = rand(1, 3);
                
                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 2);
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

                $sale->update(['total_amount' => $totalAmount - $sale->discount]);
            }

            // Criar algumas vendas canceladas
            for ($i = 0; $i < 3; $i++) {
                $sale = Sale::create([
                    'customer_id' => $customers->random()->id,
                    'user_id' => $users->random()->id,
                    'sale_number' => 'VND-' . str_pad($i + 21, 6, '0', STR_PAD_LEFT),
                    'sale_date' => now()->subDays(rand(1, 15)),
                    'payment_method' => collect(['dinheiro', 'cartao_credito', 'cartao_debito', 'pix'])->random(),
                    'status' => 'cancelada',
                    'total_amount' => 0,
                    'discount' => 0,
                    'notes' => 'Venda cancelada - ' . fake()->sentence(),
                    'canceled_at' => now()->subDays(rand(0, 10)),
                    'cancellation_reason' => 'Cancelamento de teste',
                ]);

                $totalAmount = 0;
                $numItems = rand(1, 2);
                
                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 2);
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

                $sale->update(['total_amount' => $totalAmount]);
            }
        });

        $this->command->info('Seeds de vendas criados com sucesso!');
    }
}