<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'saleItems.product'])
                    ->orderBy('created_at', 'desc');

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por período
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Busca por número da venda ou nome do cliente
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sales = $query->paginate(15)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'saleItems.product']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Cancel a sale.
     */
    public function cancel(Sale $sale)
    {
        try {
            if ($sale->status === 'canceled') {
                return redirect()->back()->with('warning', 'Esta venda já está cancelada.');
            }

            if ($sale->status === 'authorized') {
                return redirect()->back()->with('error', 'Não é possível cancelar uma venda já autorizada.');
            }

            DB::beginTransaction();

            // Restaurar estoque dos produtos
            foreach ($sale->saleItems as $item) {
                $item->product->increment('stock', $item->qty);
            }

            // Cancelar a venda
            $sale->cancel();

            DB::commit();

            return redirect()->back()->with('success', 'Venda cancelada com sucesso e estoque restaurado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao cancelar venda: ' . $e->getMessage());
        }
    }

    /**
     * Get sales statistics for dashboard.
     */
    public function statistics()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $stats = [
            'today_sales' => Sale::whereDate('created_at', $today)->where('status', '!=', 'canceled')->count(),
            'today_total' => Sale::whereDate('created_at', $today)->where('status', '!=', 'canceled')->sum('total'),
            'month_sales' => Sale::whereDate('created_at', '>=', $thisMonth)->where('status', '!=', 'canceled')->count(),
            'month_total' => Sale::whereDate('created_at', '>=', $thisMonth)->where('status', '!=', 'canceled')->sum('total'),
            'pending_sales' => Sale::where('status', 'authorized_pending')->count(),
        ];

        return response()->json($stats);
    }
}