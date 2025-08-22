<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Exibe a página principal de relatórios.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Relatório de vendas por período.
     */
    public function salesByPeriod(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'status' => 'nullable|string|in:draft,authorized_pending,authorized,canceled'
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();

        $query = Sale::with(['customer', 'saleItems.product'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Por padrão, excluir vendas canceladas
            $query->where('status', '!=', 'canceled');
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('total'),
            'average_ticket' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
            'period_from' => $dateFrom->format('d/m/Y'),
            'period_to' => $dateTo->format('d/m/Y')
        ];

        return view('reports.sales-by-period', compact('sales', 'summary', 'request'));
    }

    /**
     * Relatório de vendas por forma de pagamento.
     */
    public function salesByPaymentMethod(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from'
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();

        $salesByPayment = Sale::select(
                'payment_method',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total) as total_amount'),
                DB::raw('AVG(total) as average_ticket')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'canceled')
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();

        $totalSales = $salesByPayment->sum('total_sales');
        $totalAmount = $salesByPayment->sum('total_amount');

        $summary = [
            'total_sales' => $totalSales,
            'total_amount' => $totalAmount,
            'period_from' => $dateFrom->format('d/m/Y'),
            'period_to' => $dateTo->format('d/m/Y')
        ];

        return view('reports.sales-by-payment', compact('salesByPayment', 'summary', 'request'));
    }

    /**
     * Relatório de vendas por cliente.
     */
    public function salesByCustomer(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'limit' => 'nullable|integer|min:5|max:100'
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        $limit = $request->get('limit', 20);

        $salesByCustomer = Sale::select(
                'customer_id',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total) as total_amount'),
                DB::raw('AVG(total) as average_ticket')
            )
            ->with('customer')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'canceled')
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderBy('total_amount', 'desc')
            ->limit($limit)
            ->get();

        // Vendas sem cliente identificado
        $anonymousSales = Sale::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'canceled')
            ->whereNull('customer_id')
            ->selectRaw('COUNT(*) as total_sales, SUM(total) as total_amount, AVG(total) as average_ticket')
            ->first();

        $totalSales = $salesByCustomer->sum('total_sales') + ($anonymousSales->total_sales ?? 0);
        $totalAmount = $salesByCustomer->sum('total_amount') + ($anonymousSales->total_amount ?? 0);

        $summary = [
            'total_sales' => $totalSales,
            'total_amount' => $totalAmount,
            'period_from' => $dateFrom->format('d/m/Y'),
            'period_to' => $dateTo->format('d/m/Y'),
            'anonymous_sales' => $anonymousSales
        ];

        return view('reports.sales-by-customer', compact('salesByCustomer', 'summary', 'request'));
    }

    /**
     * Relatório de produtos mais vendidos.
     */
    public function topProducts(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'limit' => 'nullable|integer|min:5|max:100'
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        $limit = $request->get('limit', 20);

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.qty) as total_quantity'),
                DB::raw('SUM(sale_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as sales_count'),
                DB::raw('AVG(sale_items.unit_price) as average_price')
            )
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->where('sales.status', '!=', 'canceled')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        $totalQuantity = $topProducts->sum('total_quantity');
        $totalRevenue = $topProducts->sum('total_revenue');

        $summary = [
            'total_products' => $topProducts->count(),
            'total_quantity' => $totalQuantity,
            'total_revenue' => $totalRevenue,
            'period_from' => $dateFrom->format('d/m/Y'),
            'period_to' => $dateTo->format('d/m/Y')
        ];

        return view('reports.top-products', compact('topProducts', 'summary', 'request'));
    }

    /**
     * Exportar relatório de vendas por período para CSV.
     */
    public function exportSalesByPeriodCsv(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'status' => 'nullable|string|in:draft,authorized_pending,authorized,canceled'
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();

        $query = Sale::with(['customer', 'saleItems.product'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', '!=', 'canceled');
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        $filename = 'vendas_por_periodo_' . $dateFrom->format('Y-m-d') . '_' . $dateTo->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'Número da Venda',
                'Data/Hora',
                'Cliente',
                'Documento Cliente',
                'Forma de Pagamento',
                'Status',
                'Subtotal',
                'Desconto',
                'Total',
                'Produtos'
            ], ';');

            foreach ($sales as $sale) {
                $products = $sale->saleItems->map(function($item) {
                    return $item->product->name . ' (Qtd: ' . $item->qty . ', Unit: R$ ' . number_format($item->unit_price, 2, ',', '.') . ')';
                })->implode(' | ');

                fputcsv($file, [
                    $sale->sale_number,
                    $sale->created_at->format('d/m/Y H:i:s'),
                    $sale->customer ? $sale->customer->name : 'Cliente não identificado',
                    $sale->customer ? $sale->customer->document : '-',
                    ucfirst(str_replace('_', ' ', $sale->payment_method)),
                    ucfirst($sale->status),
                    'R$ ' . number_format($sale->subtotal, 2, ',', '.'),
                    'R$ ' . number_format($sale->discount, 2, ',', '.'),
                    'R$ ' . number_format($sale->total, 2, ',', '.'),
                    $products
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar relatório de vendas por período para PDF.
     */
    public function exportSalesByPeriodPdf(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'status' => 'nullable|string|in:draft,authorized_pending,authorized,canceled'
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();

        $query = Sale::with(['customer', 'saleItems.product'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', '!=', 'canceled');
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('total'),
            'average_ticket' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
            'period_from' => $dateFrom->format('d/m/Y'),
            'period_to' => $dateTo->format('d/m/Y')
        ];

        $pdf = Pdf::loadView('reports.pdf.sales-by-period', compact('sales', 'summary'))
            ->setPaper('a4', 'portrait');

        $filename = 'vendas_por_periodo_' . $dateFrom->format('Y-m-d') . '_' . $dateTo->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Exportar relatório de vendas por forma de pagamento para CSV.
     */
    public function exportSalesByPaymentCsv(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from'
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();

        $salesByPayment = Sale::select(
                'payment_method',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total) as total_amount'),
                DB::raw('AVG(total) as average_ticket')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'canceled')
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();

        $filename = 'vendas_por_pagamento_' . $dateFrom->format('Y-m-d') . '_' . $dateTo->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($salesByPayment) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'Forma de Pagamento',
                'Quantidade de Vendas',
                'Valor Total',
                'Ticket Médio'
            ], ';');

            foreach ($salesByPayment as $payment) {
                fputcsv($file, [
                    ucfirst(str_replace('_', ' ', $payment->payment_method)),
                    $payment->total_sales,
                    'R$ ' . number_format($payment->total_amount, 2, ',', '.'),
                    'R$ ' . number_format($payment->average_ticket, 2, ',', '.')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar relatório de produtos mais vendidos para CSV.
     */
    public function exportTopProductsCsv(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'limit' => 'nullable|integer|min:5|max:100',
            'order_by' => 'nullable|string|in:quantity,revenue'
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        $limit = $request->get('limit', 20);
        $orderBy = $request->get('order_by', 'quantity') == 'quantity' ? 'total_quantity' : 'total_revenue';

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.code',
                'products.price',
                'products.stock_quantity',
                DB::raw('SUM(sale_items.qty) as total_quantity'),
                DB::raw('SUM(sale_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as sales_count'),
                DB::raw('AVG(sale_items.unit_price) as average_price')
            )
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->where('sales.status', '!=', 'canceled')
            ->groupBy('products.id', 'products.name', 'products.code', 'products.price', 'products.stock_quantity')
            ->orderBy($orderBy, 'desc')
            ->limit($limit)
            ->get();

        $filename = 'produtos_mais_vendidos_' . $dateFrom->format('Y-m-d') . '_' . $dateTo->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($topProducts) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'Posição',
                'Produto',
                'Código',
                'Preço Unitário',
                'Quantidade Vendida',
                'Receita Total',
                'Número de Vendas',
                'Preço Médio de Venda',
                'Estoque Atual'
            ], ';');

            foreach ($topProducts as $index => $product) {
                fputcsv($file, [
                    $index + 1,
                    $product->name,
                    $product->code,
                    'R$ ' . number_format($product->price, 2, ',', '.'),
                    $product->total_quantity,
                    'R$ ' . number_format($product->total_revenue, 2, ',', '.'),
                    $product->sales_count,
                    'R$ ' . number_format($product->average_price, 2, ',', '.'),
                    $product->stock_quantity
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
