<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Produtos Mais Vendidos</title>
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
        
        .chart-container {
            margin: 20px 0;
            text-align: center;
        }
        
        .chart-bar {
            display: inline-block;
            width: 100%;
            margin: 5px 0;
            background-color: #f8f9fa;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .chart-bar-fill {
            height: 25px;
            background-color: #28a745;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        
        .chart-label {
            font-size: 10px;
            margin-bottom: 2px;
            font-weight: bold;
        }
        
        .position-badge {
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 10px;
            min-width: 25px;
            text-align: center;
            display: inline-block;
        }
        
        .position-badge.top-1 {
            background-color: #ffd700;
            color: #333;
        }
        
        .position-badge.top-2 {
            background-color: #c0c0c0;
            color: #333;
        }
        
        .position-badge.top-3 {
            background-color: #cd7f32;
            color: white;
        }
        
        .top-products-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .product-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            width: 48%;
            text-align: center;
        }
        
        .product-card h4 {
            color: #007bff;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .product-card .stats {
            font-size: 10px;
            color: #666;
        }
        
        .product-card .value {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
            margin: 5px 0;
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
            <h1>NFCeBox - Produtos Mais Vendidos</h1>
            <p>Período: {{ $summary['period_from'] }} a {{ $summary['period_to'] }}</p>
            <p>Ordenação: {{ $orderBy === 'qty' ? 'Por Quantidade' : 'Por Receita' }} | Limite: {{ $limit }} produtos</p>
            <p>Gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>

        <!-- Resumo -->
        <div class="summary">
            <div class="summary-item">
                <h3>{{ $summary['total_products'] }}</h3>
                <p>Produtos Vendidos</p>
            </div>
            <div class="summary-item">
                <h3>{{ number_format($summary['total_qty']) }}</h3>
                <p>Quantidade Total</p>
            </div>
            <div class="summary-item">
                <h3>R$ {{ number_format($summary['total_revenue'], 2, ',', '.') }}</h3>
                <p>Receita Total</p>
            </div>
        </div>

        <!-- Top 5 Produtos em Cards -->
        @if(count($products) > 0)
        <div class="table-container">
            <div class="table-title">Top 5 Produtos</div>
            <div class="top-products-cards">
                @foreach($products->take(5) as $index => $product)
                <div class="product-card">
                    <div class="position-badge {{ $index === 0 ? 'top-1' : ($index === 1 ? 'top-2' : ($index === 2 ? 'top-3' : '')) }}">
                        {{ $index + 1 }}º
                    </div>
                    <h4>{{ $product->name }}</h4>
                    <div class="stats">Código: {{ $product->code }}</div>
                    <div class="value">{{ number_format($product->total_qty) }} unidades</div>
                    <div class="value">R$ {{ number_format($product->total_revenue, 2, ',', '.') }}</div>
                    <div class="stats">{{ number_format($product->revenue_percentage, 1) }}% da receita</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Gráfico de Barras dos Top 10 -->
        @if(count($products) > 0)
        <div class="table-container">
            <div class="table-title">Top 10 - Distribuição {{ $orderBy === 'qty' ? 'por Quantidade' : 'por Receita' }}</div>
            <div class="chart-container">
                @php
                    $top10 = $products->take(10);
                    $maxValue = $orderBy === 'qty' ? $top10->max('total_qty') : $top10->max('total_revenue');
                @endphp
                @foreach($top10 as $index => $product)
                <div style="margin-bottom: 15px;">
                    <div class="chart-label">
                        {{ $index + 1 }}º {{ $product->name }} ({{ $product->code }})
                        @if($orderBy === 'qty')
                            - {{ number_format($product->total_qty) }} unidades
                        @else
                            - R$ {{ number_format($product->total_revenue, 2, ',', '.') }}
                        @endif
                    </div>
                    <div class="chart-bar">
                        @php
                            $value = $orderBy === 'qty' ? $product->total_qty : $product->total_revenue;
                            $percentage = ($value / $maxValue) * 100;
                        @endphp
                        <div class="chart-bar-fill" style="width: {{ $percentage }}%;">
                            @if($orderBy === 'qty')
                                {{ number_format($product->total_qty) }}
                            @else
                                R$ {{ number_format($product->total_revenue, 2, ',', '.') }}
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tabela Completa -->
        <div class="table-container">
            <div class="table-title">Ranking Completo de Produtos</div>
            
            @if(count($products) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Produto</th>
                            <th>Código</th>
                            <th>Categoria</th>
                            <th>Preço Unit.</th>
                            <th>Qtd. Vendida</th>
                            <th>Receita Total</th>
                            <th>% Receita</th>
                            <th>Estoque Atual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $index => $product)
                        <tr>
                            <td class="text-center">
                                <span class="position-badge {{ $index === 0 ? 'top-1' : ($index === 1 ? 'top-2' : ($index === 2 ? 'top-3' : '')) }}">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td class="text-center">{{ $product->code }}</td>
                            <td class="text-center">
                                @if($product->category)
                                    {{ $product->category->name }}
                                @else
                                    <em>Sem categoria</em>
                                @endif
                            </td>
                            <td class="text-right">R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                            <td class="text-center font-weight-bold">{{ number_format($product->total_qty) }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($product->total_revenue, 2, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($product->revenue_percentage, 1) }}%</td>
                            <td class="text-center">
                                @if($product->stock_qty > 0)
                                    <span style="color: #28a745; font-weight: bold;">{{ number_format($product->stock_qty) }}</span>
                                @elseif($product->stock_qty == 0)
                                    <span style="color: #ffc107; font-weight: bold;">0</span>
                                @else
                                    <span style="color: #dc3545; font-weight: bold;">{{ number_format($product->stock_qty) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #343a40; color: white; font-weight: bold;">
                            <td colspan="5" class="text-center">TOTAL GERAL</td>
                            <td class="text-center">{{ number_format($products->sum('total_qty')) }}</td>
                            <td class="text-right">R$ {{ number_format($products->sum('total_revenue'), 2, ',', '.') }}</td>
                            <td class="text-center">100.0%</td>
                            <td class="text-center">-</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="no-data">
                    <h3>Nenhum produto encontrado</h3>
                    <p>Não há produtos vendidos no período selecionado.</p>
                </div>
            @endif
        </div>

        <!-- Análise por Categoria -->
        @if(count($products) > 0)
        <div class="table-container" style="page-break-before: always;">
            <div class="table-title">Análise por Categoria</div>
            
            @php
                $categoryData = [];
                foreach($products as $product) {
                    $categoryName = $product->category ? $product->category->name : 'Sem categoria';
                    if (!isset($categoryData[$categoryName])) {
                        $categoryData[$categoryName] = [
                            'name' => $categoryName,
                            'products_count' => 0,
                            'total_qty' => 0,
                            'total_revenue' => 0
                        ];
                    }
                    $categoryData[$categoryName]['products_count']++;
                    $categoryData[$categoryName]['total_qty'] += $product->total_qty;
                    $categoryData[$categoryName]['total_revenue'] += $product->total_revenue;
                }
                
                // Ordenar por receita
                uasort($categoryData, function($a, $b) {
                    return $b['total_revenue'] <=> $a['total_revenue'];
                });
                
                $totalCategoryRevenue = array_sum(array_column($categoryData, 'total_revenue'));
            @endphp
            
            <table>
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Qtd. Produtos</th>
                        <th>Qtd. Total Vendida</th>
                        <th>Receita Total</th>
                        <th>% da Receita</th>
                        <th>Ticket Médio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categoryData as $category)
                    <tr>
                        <td><strong>{{ $category['name'] }}</strong></td>
                        <td class="text-center">{{ $category['products_count'] }}</td>
                        <td class="text-center font-weight-bold">{{ number_format($category['total_qty']) }}</td>
                        <td class="text-right font-weight-bold">R$ {{ number_format($category['total_revenue'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ number_format(($category['total_revenue'] / $totalCategoryRevenue) * 100, 1) }}%</td>
                        <td class="text-right">R$ {{ number_format($category['total_revenue'] / $category['total_qty'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #343a40; color: white; font-weight: bold;">
                        <td class="text-center">TOTAL</td>
                        <td class="text-center">{{ array_sum(array_column($categoryData, 'products_count')) }}</td>
                        <td class="text-center">{{ number_format(array_sum(array_column($categoryData, 'total_qty'))) }}</td>
                        <td class="text-right">R$ {{ number_format($totalCategoryRevenue, 2, ',', '.') }}</td>
                        <td class="text-center">100.0%</td>
                        <td class="text-right">R$ {{ number_format($totalCategoryRevenue / array_sum(array_column($categoryData, 'total_qty')), 2, ',', '.') }}</td>
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