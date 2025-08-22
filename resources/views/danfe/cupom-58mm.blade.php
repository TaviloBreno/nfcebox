<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DANFE NFCe - {{ $formatted_nfce_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            width: 58mm;
            margin: 0;
            padding: 1mm;
            color: #000;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
        }
        
        .company-name {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 1mm;
            text-transform: uppercase;
        }
        
        .company-info {
            font-size: 8px;
            line-height: 1.2;
        }
        
        .nfce-info {
            text-align: center;
            margin: 2mm 0;
            padding: 2mm 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        
        .nfce-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 1mm;
        }
        
        .items {
            margin: 2mm 0;
        }
        
        .item {
            margin-bottom: 2mm;
            padding-bottom: 1mm;
            border-bottom: 1px dotted #ccc;
            font-size: 8px;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 0.5mm;
            word-wrap: break-word;
        }
        
        .item-details {
            font-size: 7px;
        }
        
        .item-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5mm;
        }
        
        .totals {
            margin: 2mm 0;
            padding: 2mm 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
            font-size: 8px;
        }
        
        .total-final {
            font-weight: bold;
            font-size: 11px;
            border-top: 1px solid #000;
            padding-top: 1mm;
            margin-top: 1mm;
        }
        
        .payment {
            text-align: center;
            margin: 2mm 0;
            font-size: 9px;
            font-weight: bold;
        }
        
        .qrcode {
            text-align: center;
            margin: 3mm 0;
        }
        
        .qrcode svg {
            width: 20mm;
            height: 20mm;
        }
        
        .access-key {
            text-align: center;
            margin: 2mm 0;
            font-size: 6px;
            word-break: break-all;
            line-height: 1.1;
        }
        
        .protocol {
            text-align: center;
            margin: 2mm 0;
            font-size: 7px;
        }
        
        .footer {
            text-align: center;
            margin-top: 3mm;
            padding-top: 2mm;
            border-top: 1px dashed #000;
            font-size: 6px;
            line-height: 1.2;
        }
        
        .customer {
            margin: 2mm 0;
            font-size: 8px;
            text-align: center;
        }
        
        .section-title {
            font-weight: bold;
            text-align: center;
            margin: 2mm 0 1mm 0;
            font-size: 8px;
        }
        
        @page {
            margin: 0;
            size: 58mm auto;
        }
        
        @media print {
            body {
                width: 58mm;
                margin: 0;
                padding: 1mm;
            }
        }
    </style>
</head>
<body>
    <!-- Cabeçalho da Empresa -->
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'EMPRESA LTDA' }}</div>
        <div class="company-info">
            CNPJ: {{ $company->cnpj ?? '00.000.000/0001-00' }}<br>
            {{ $company->address ?? 'Endereço não informado' }}<br>
            {{ $company->city ?? 'Cidade' }} - {{ $company->state ?? 'UF' }}<br>
            CEP: {{ $company->zip_code ?? '00000-000' }}<br>
            @if($company->phone ?? false)
                Fone: {{ $company->phone }}<br>
            @endif
        </div>
    </div>

    <!-- Informações da NFCe -->
    <div class="nfce-info">
        <div class="nfce-title">CUPOM FISCAL ELETRÔNICO</div>
        <div>NFCe nº {{ $formatted_nfce_number }}</div>
        <div>{{ $sale->created_at->format('d/m/Y H:i:s') }}</div>
    </div>

    <!-- Cliente -->
    @if($customer)
    <div class="customer">
        <div class="section-title">CONSUMIDOR</div>
        {{ $customer->name }}<br>
        @if($customer->document)
            CPF/CNPJ: {{ $customer->document }}
        @endif
    </div>
    @endif

    <!-- Itens -->
    <div class="items">
        <div class="section-title">ITENS</div>
        @foreach($items as $item)
        <div class="item">
            <div class="item-name">{{ $item->product->name }}</div>
            <div class="item-details">
                <div class="item-line">
                    <span>Qtd:</span>
                    <span>{{ number_format($item->quantity, 0) }}</span>
                </div>
                <div class="item-line">
                    <span>Valor Unit:</span>
                    <span>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</span>
                </div>
                <div class="item-line">
                    <span>Total:</span>
                    <span>R$ {{ number_format($item->total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Totais -->
    <div class="totals">
        <div class="total-line">
            <span>Qtd. total de itens:</span>
            <span>{{ $items->sum('quantity') }}</span>
        </div>
        <div class="total-line total-final">
            <span>TOTAL R$:</span>
            <span>{{ number_format($sale->total, 2, ',', '.') }}</span>
        </div>
    </div>

    <!-- Forma de Pagamento -->
    <div class="payment">
        FORMA DE PAGAMENTO<br>
        {{ $payment_method_label }}: R$ {{ number_format($sale->total, 2, ',', '.') }}
    </div>

    <!-- QR Code -->
    <div class="qrcode">
        {!! $qrcode !!}
        <div style="font-size: 6px; margin-top: 1mm;">Consulte pela chave de acesso em<br>fazenda.sp.gov.br</div>
    </div>

    <!-- Chave de Acesso -->
    <div class="access-key">
        <strong>CHAVE DE ACESSO:</strong><br>
        {{ $formatted_access_key }}
    </div>

    <!-- Protocolo -->
    @if($sale->protocol)
    <div class="protocol">
        <strong>Protocolo de autorização:</strong><br>
        {{ $sale->protocol }}<br>
        {{ $sale->authorized_at ? $sale->authorized_at->format('d/m/Y H:i:s') : 'N/A' }}
    </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        Tributos totais incidentes conforme<br>
        Lei Federal 12.741/2012: R$ 0,00<br>
        <br>
        OBRIGADO PELA PREFERÊNCIA!
    </div>
</body>
</html>