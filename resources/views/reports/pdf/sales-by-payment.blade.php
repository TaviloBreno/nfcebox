<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Vendas por Forma de Pagamento</title>
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
            font-size: 11px;
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
        
        .payment-method {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
            text-align: center;
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
            background-color: #007bff;
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
        
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <h1>NFCeBox - Vendas por Forma de Pagamento</h1>
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
                <h3>{{ count($paymentData) }}</h3>
                <p>Formas de Pagamento</p>
            </div>
        </div>

        <!-- Gráfico de Barras Simples -->
        @if(count($paymentData) > 0)
        <div class="table-container">
            <div class="table-title">Distribuição por Forma de Pagamento</div>
            <div class="chart-container">
                @php
                    $maxValue = max(array_column($paymentData, 'total_amount'));
                @endphp
                @foreach($paymentData as $payment)
                <div style="margin-bottom: 15px;">
                    <div class="chart-label">
                        {{ ucfirst(str_replace('_', ' ', $payment['payment_method'])) }} - 
                        {{ $payment['sales_count'] }} vendas ({{ number_format($payment['percentage'], 1) }}%)
                    </div>
                    <div class="chart-bar">
                        <div class="chart-bar-fill" style="width: {{ ($payment['total_amount'] / $maxValue) * 100 }}%;">
                            R$ {{ number_format($payment['total_amount'], 2, ',', '.') }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tabela Detalhada -->
        <div class="table-container">
            <div class="table-title">Detalhamento por Forma de Pagamento</div>
            
            @if(count($paymentData) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Forma de Pagamento</th>
                            <th>Qtd. Vendas</th>
                            <th>Valor Total</th>
                            <th>Ticket Médio</th>
                            <th>Participação (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentData as $payment)
                        <tr>
                            <td>
                                <span class="payment-method {{ $payment['payment_method'] }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment['payment_method'])) }}
                                </span>
                            </td>
                            <td class="text-center font-weight-bold">{{ $payment['sales_count'] }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($payment['total_amount'], 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($payment['average_ticket'], 2, ',', '.') }}</td>
                            <td class="text-center">
                                <strong>{{ number_format($payment['percentage'], 1) }}%</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #343a40; color: white; font-weight: bold;">
                            <td class="text-center">TOTAL GERAL</td>
                            <td class="text-center">{{ array_sum(array_column($paymentData, 'sales_count')) }}</td>
                            <td class="text-right">R$ {{ number_format(array_sum(array_column($paymentData, 'total_amount')), 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format(array_sum(array_column($paymentData, 'total_amount')) / array_sum(array_column($paymentData, 'sales_count')), 2, ',', '.') }}</td>
                            <td class="text-center">100.0%</td>
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

        <!-- Detalhamento das Vendas por Forma de Pagamento -->
        @if(count($paymentData) > 0)
        <div class="table-container" style="page-break-before: always;">
            <div class="table-title">Vendas Detalhadas por Forma de Pagamento</div>
            
            @foreach($paymentData as $payment)
            <div style="margin-bottom: 30px;">
                <h3 style="background-color: #f8f9fa; padding: 10px; margin-bottom: 10px; border-left: 4px solid #007bff;">
                    <span class="payment-method {{ $payment['payment_method'] }}">
                        {{ ucfirst(str_replace('_', ' ', $payment['payment_method'])) }}
                    </span>
                    - {{ $payment['sales_count'] }} vendas
                </h3>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nº Venda</th>
                            <th>Data/Hora</th>
                            <th>Cliente</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $paymentSales = $sales->where('payment_method', $payment['payment_method']);
                        @endphp
                        @foreach($paymentSales as $sale)
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
                                @switch($sale->status)
                                    @case('draft')
                                        <span style="background-color: #ffc107; color: #212529; padding: 2px 6px; border-radius: 3px; font-size: 9px;">Rascunho</span>
                                        @break
                                    @case('authorized_pending')
                                        <span style="background-color: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 9px;">Pendente</span>
                                        @break
                                    @case('authorized')
                                        <span style="background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 9px;">Autorizada</span>
                                        @break
                                    @case('canceled')
                                        <span style="background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 9px;">Cancelada</span>
                                        @break
                                    @default
                                        <span style="background-color: #6c757d; color: white; padding: 2px 6px; border-radius: 3px; font-size: 9px;">{{ ucfirst($sale->status) }}</span>
                                @endswitch
                            </td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8f9fa; font-weight: bold;">
                            <td colspan="4" class="text-center">Subtotal {{ ucfirst(str_replace('_', ' ', $payment['payment_method'])) }}</td>
                            <td class="text-right">R$ {{ number_format($payment['total_amount'], 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endforeach
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