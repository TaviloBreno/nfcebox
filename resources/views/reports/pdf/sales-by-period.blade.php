<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Vendas por Período</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #007bff;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .summary-item {
            text-align: center;
            flex: 1;
        }
        
        .summary-item h3 {
            color: #007bff;
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .summary-item p {
            color: #666;
            font-size: 12px;
        }
        
        .table-container {
            margin-top: 20px;
        }
        
        .table-title {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status.draft {
            background-color: #ffc107;
            color: #212529;
        }
        
        .status.authorized {
            background-color: #28a745;
            color: white;
        }
        
        .status.authorized_pending {
            background-color: #17a2b8;
            color: white;
        }
        
        .status.canceled {
            background-color: #dc3545;
            color: white;
        }
        
        .payment-method {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .payment-method.dinheiro {
            background-color: #28a745;
            color: white;
        }
        
        .payment-method.cartao_credito {
            background-color: #007bff;
            color: white;
        }
        
        .payment-method.cartao_debito {
            background-color: #17a2b8;
            color: white;
        }
        
        .payment-method.pix {
            background-color: #ffc107;
            color: #212529;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-weight-bold {
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-data h3 {
            margin-bottom: 10px;
        }
        
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <h1>NFCeBox - Relatório de Vendas</h1>
            <p>Período: {{ $summary['period_from'] }} a {{ $summary['period_to'] }}</p>
            <p>Gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>

        <!-- Resumo -->
        <div class="summary">
            <div class="summary-item">
                <h3>{{ $summary['total_sales'] }}</h3>
                <p>Total de Vendas</p>
            </div>
            <div class="summary-item">
                <h3>R$ {{ number_format($summary['total_amount'], 2, ',', '.') }}</h3>
                <p>Valor Total</p>
            </div>
            <div class="summary-item">
                <h3>R$ {{ number_format($summary['average_ticket'], 2, ',', '.') }}</h3>
                <p>Ticket Médio</p>
            </div>
        </div>

        <!-- Tabela de Vendas -->
        <div class="table-container">
            <div class="table-title">Detalhamento das Vendas</div>
            
            @if($sales->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Nº Venda</th>
                            <th>Data/Hora</th>
                            <th>Cliente</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            <th>Subtotal</th>
                            <th>Desconto</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $sale->sale_number }}</td>
                            <td class="text-center">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($sale->customer)
                                    <strong>{{ $sale->customer->name }}</strong><br>
                                    <small>{{ $sale->customer->document }}</small>
                                @else
                                    <em>Cliente não identificado</em>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="payment-method {{ $sale->payment_method }}">
                                    {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="status {{ $sale->status }}">
                                    @switch($sale->status)
                                        @case('draft')
                                            Rascunho
                                            @break
                                        @case('authorized_pending')
                                            Pendente
                                            @break
                                        @case('authorized')
                                            Autorizada
                                            @break
                                        @case('canceled')
                                            Cancelada
                                            @break
                                        @default
                                            {{ ucfirst($sale->status) }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="text-right">R$ {{ number_format($sale->subtotal, 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($sale->discount, 2, ',', '.') }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #343a40; color: white; font-weight: bold;">
                            <td colspan="5" class="text-center">TOTAIS</td>
                            <td class="text-right">R$ {{ number_format($sales->sum('subtotal'), 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($sales->sum('discount'), 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($sales->sum('total'), 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="no-data">
                    <h3>Nenhuma venda encontrada</h3>
                    <p>Não há vendas no período selecionado.</p>
                </div>
            @endif
        </div>

        <!-- Detalhamento por Produtos (se houver vendas) -->
        @if($sales->count() > 0)
        <div class="table-container" style="page-break-before: always;">
            <div class="table-title">Produtos Vendidos</div>
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Código</th>
                        <th>Qtd. Total</th>
                        <th>Valor Unit. Médio</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $productSummary = [];
                        foreach($sales as $sale) {
                            foreach($sale->saleItems as $item) {
                                $productId = $item->product->id;
                                if (!isset($productSummary[$productId])) {
                                    $productSummary[$productId] = [
                                        'name' => $item->product->name,
                                        'code' => $item->product->code,
                                        'total_qty' => 0,
                                        'total_value' => 0,
                                        'prices' => []
                                    ];
                                }
                                $productSummary[$productId]['total_qty'] += $item->qty;
                                $productSummary[$productId]['total_value'] += $item->total;
                                $productSummary[$productId]['prices'][] = $item->unit_price;
                            }
                        }
                        
                        // Ordenar por quantidade vendida (decrescente)
                        uasort($productSummary, function($a, $b) {
                            return $b['total_qty'] <=> $a['total_qty'];
                        });
                    @endphp
                    
                    @foreach($productSummary as $product)
                    <tr>
                        <td><strong>{{ $product['name'] }}</strong></td>
                        <td class="text-center">{{ $product['code'] }}</td>
                        <td class="text-center font-weight-bold">{{ number_format($product['total_qty']) }}</td>
                        <td class="text-right">R$ {{ number_format(array_sum($product['prices']) / count($product['prices']), 2, ',', '.') }}</td>
                        <td class="text-right font-weight-bold">R$ {{ number_format($product['total_value'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #343a40; color: white; font-weight: bold;">
                        <td colspan="2" class="text-center">TOTAL GERAL</td>
                        <td class="text-center">{{ number_format(array_sum(array_column($productSummary, 'total_qty'))) }}</td>
                        <td class="text-right">-</td>
                        <td class="text-right">R$ {{ number_format(array_sum(array_column($productSummary, 'total_value')), 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Rodapé -->
        <div class="footer">
            <p><strong>NFCeBox</strong> - Sistema de Gestão de NFC-e</p>
            <p>Relatório gerado automaticamente em {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>