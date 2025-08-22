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
            // Verificações iniciais sem lock
            if ($sale->status === 'canceled') {
                return redirect()->back()->with('warning', 'Esta venda já está cancelada.');
            }

            if ($sale->status === 'authorized') {
                return redirect()->back()->with('error', 'Não é possível cancelar uma venda já autorizada.');
            }

            DB::beginTransaction();

            // Lock da venda para evitar race conditions
            $lockedSale = Sale::lockForUpdate()->find($sale->id);
            
            // Verificar novamente após o lock
            if ($lockedSale->status === 'canceled') {
                DB::rollBack();
                return redirect()->back()->with('warning', 'Esta venda já foi cancelada por outro processo.');
            }

            if ($lockedSale->status === 'authorized') {
                DB::rollBack();
                return redirect()->back()->with('error', 'Esta venda já foi autorizada e não pode ser cancelada.');
            }

            // Restaurar estoque dos produtos com lock
            foreach ($lockedSale->saleItems as $item) {
                $product = $item->product()->lockForUpdate()->first();
                $product->increment('stock', $item->qty);
                
                \Log::info('Estoque restaurado no cancelamento', [
                    'sale_id' => $lockedSale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_restored' => $item->qty,
                    'new_stock' => $product->fresh()->stock
                ]);
            }

            // Cancelar a venda
            $lockedSale->update(['status' => 'canceled']);

            DB::commit();

            \Log::info('Venda cancelada com sucesso', [
                'sale_id' => $lockedSale->id,
                'sale_number' => $lockedSale->number
            ]);

            return redirect()->back()->with('success', 'Venda cancelada com sucesso e estoque restaurado.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erro ao cancelar venda', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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