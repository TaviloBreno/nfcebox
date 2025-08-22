<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Cria uma grande quantidade de dados para testes de performance.
     */
    public function run(): void
    {
        $this->command->info('Iniciando criação de dados para teste de performance...');
        
        DB::transaction(function () {
            // Criar usuários adicionais
            $this->command->info('Criando usuários...');
            User::factory()->count(20)->create();
            
            // Criar clientes em lote
            $this->command->info('Criando clientes...');
            Customer::factory()->count(500)->create();
            
            // Criar produtos em lote
            $this->command->info('Criando produtos...');
            Product::factory()->count(200)->create();
            
            // Criar vendas dos últimos 2 anos
            $this->command->info('Criando histórico de vendas...');
            $this->createLargeSalesHistory();
        });

        $this->command->info('Seeds de performance criados com sucesso!');
    }

    private function createLargeSalesHistory()
    {
        $customers = Customer::all();
        $products = Product::all();
        $users = User::all();

        if ($customers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Não há dados suficientes para criar o histórico de vendas.');
            return;
        }

        $paymentMethods = ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix'];
        $saleNumber = Sale::max('id') ?? 0;
        $batchSize = 100;
        $totalSales = 0;

        // Criar vendas dos últimos 24 meses
        for ($month = 23; $month >= 0; $month--) {
            $startDate = now()->subMonths($month)->startOfMonth();
            $endDate = now()->subMonths($month)->endOfMonth();
            
            // Mais vendas nos meses recentes e durante feriados/datas comemorativas
            $salesCount = $this->getSalesCountForMonth($month);
            
            $this->command->info("Criando {$salesCount} vendas para " . $startDate->format('M/Y'));
            
            $salesBatch = [];
            $itemsBatch = [];
            
            for ($i = 0; $i < $salesCount; $i++) {
                $saleDate = $startDate->copy()->addDays(rand(0, $endDate->diffInDays($startDate)));
                
                // Distribuição de status: 90% finalizadas, 7% pendentes, 3% canceladas
                $statusRand = rand(1, 100);
                if ($statusRand <= 90) {
                    $status = 'finalizada';
                } elseif ($statusRand <= 97) {
                    $status = 'pendente';
                } else {
                    $status = 'cancelada';
                }

                $discount = rand(0, 50);
                $saleId = ++$saleNumber;
                
                $salesBatch[] = [
                    'id' => $saleId,
                    'customer_id' => $customers->random()->id,
                    'user_id' => $users->random()->id,
                    'sale_number' => 'VND-' . str_pad($saleId, 8, '0', STR_PAD_LEFT),
                    'sale_date' => $saleDate,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => $status,
                    'total_amount' => 0, // Será atualizado após inserir itens
                    'discount' => $discount,
                    'notes' => $this->generateSaleNotes($status),
                    'canceled_at' => $status === 'cancelada' ? $saleDate->copy()->addHours(rand(1, 72)) : null,
                    'cancellation_reason' => $status === 'cancelada' ? $this->generateCancellationReason() : null,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ];

                // Adicionar itens à venda
                $numItems = rand(1, 6);
                $totalAmount = 0;
                
                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 4);
                    $unitPrice = $product->price;
                    $subtotal = $quantity * $unitPrice;
                    $totalAmount += $subtotal;

                    $itemsBatch[] = [
                        'sale_id' => $saleId,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'created_at' => $saleDate,
                        'updated_at' => $saleDate,
                    ];
                }

                // Atualizar total da venda
                $salesBatch[count($salesBatch) - 1]['total_amount'] = max(0, $totalAmount - $discount);

                // Inserir em lotes para melhor performance
                if (count($salesBatch) >= $batchSize) {
                    Sale::insert($salesBatch);
                    SaleItem::insert($itemsBatch);
                    $totalSales += count($salesBatch);
                    $salesBatch = [];
                    $itemsBatch = [];
                }
            }

            // Inserir lote restante
            if (!empty($salesBatch)) {
                Sale::insert($salesBatch);
                SaleItem::insert($itemsBatch);
                $totalSales += count($salesBatch);
            }
        }

        $this->command->info("Total de {$totalSales} vendas criadas.");
    }

    private function getSalesCountForMonth($monthsAgo)
    {
        $baseCount = 50;
        
        // Mais vendas nos meses recentes
        if ($monthsAgo <= 3) {
            $baseCount = 150;
        } elseif ($monthsAgo <= 6) {
            $baseCount = 120;
        } elseif ($monthsAgo <= 12) {
            $baseCount = 80;
        }
        
        // Aumentar vendas em meses de alta temporada (Nov, Dez, Jan, Mai)
        $month = now()->subMonths($monthsAgo)->month;
        if (in_array($month, [11, 12, 1, 5])) {
            $baseCount = (int)($baseCount * 1.5);
        }
        
        // Adicionar variação aleatória
        return $baseCount + rand(-20, 30);
    }

    private function generateSaleNotes($status)
    {
        $notes = [
            'finalizada' => [
                'Venda realizada com sucesso',
                'Cliente satisfeito com o atendimento',
                'Produtos entregues conforme solicitado',
                'Pagamento processado sem problemas',
                'Venda concluída normalmente',
            ],
            'pendente' => [
                'Aguardando confirmação do pagamento',
                'Cliente solicitou prazo para decisão',
                'Verificando disponibilidade do produto',
                'Pendente aprovação do crédito',
                'Aguardando documentação do cliente',
            ],
            'cancelada' => [
                'Cancelada a pedido do cliente',
                'Produto não disponível no estoque',
                'Problema com forma de pagamento',
                'Cliente desistiu da compra',
                'Cancelamento por erro no sistema',
            ],
        ];

        return $notes[$status][array_rand($notes[$status])];
    }

    private function generateCancellationReason()
    {
        $reasons = [
            'Cliente desistiu da compra',
            'Produto fora de estoque',
            'Problema com pagamento',
            'Erro no pedido',
            'Solicitação do cliente',
            'Produto com defeito',
            'Prazo de entrega não atendido',
            'Preço alterado',
        ];

        return $reasons[array_rand($reasons)];
    }
}