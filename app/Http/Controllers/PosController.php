<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Sales\SaleNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PosController extends Controller
{
    /**
     * Exibe a tela principal do PDV.
     */
    public function index()
    {
        $customers = Customer::orderBy('name')->get();
        $cart = Session::get('pos_cart', []);
        $cartTotal = $this->calculateCartTotal($cart);
        
        return view('pos.index', compact('customers', 'cart', 'cartTotal'));
    }
    
    /**
     * Busca clientes por nome ou documento.
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('document', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'document']);
            
        return response()->json($customers);
    }
    
    /**
     * Busca produtos por nome ou SKU via AJAX.
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $products = Product::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'sku', 'price', 'stock', 'unit']);
            
        return response()->json($products);
    }
    
    /**
     * Adiciona produto ao carrinho.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $product = Product::findOrFail($request->product_id);
        
        // Verifica estoque disponível
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Estoque insuficiente. Disponível: ' . $product->stock
            ]);
        }
        
        $cart = Session::get('pos_cart', []);
        $productId = $product->id;
        
        // Se produto já existe no carrinho, soma a quantidade
        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + $request->quantity;
            
            // Verifica se a nova quantidade não excede o estoque
            if ($newQuantity > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantidade total excede o estoque disponível. Disponível: ' . $product->stock
                ]);
            }
            
            $cart[$productId]['quantity'] = $newQuantity;
            $cart[$productId]['subtotal'] = $newQuantity * $product->price;
        } else {
            // Adiciona novo produto ao carrinho
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => $request->quantity,
                'subtotal' => $product->price * $request->quantity,
                'stock' => $product->stock
            ];
        }
        
        Session::put('pos_cart', $cart);
        
        return response()->json([
            'success' => true,
            'message' => 'Produto adicionado ao carrinho',
            'cart' => $cart,
            'cartTotal' => $this->calculateCartTotal($cart)
        ]);
    }
    
    /**
     * Atualiza quantidade de um item no carrinho.
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $cart = Session::get('pos_cart', []);
        $productId = $request->product_id;
        
        if (!isset($cart[$productId])) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado no carrinho'
            ]);
        }
        
        // Verifica estoque disponível
        if ($request->quantity > $cart[$productId]['stock']) {
            return response()->json([
                'success' => false,
                'message' => 'Quantidade excede o estoque disponível. Disponível: ' . $cart[$productId]['stock']
            ]);
        }
        
        $cart[$productId]['quantity'] = $request->quantity;
        $cart[$productId]['subtotal'] = $request->quantity * $cart[$productId]['price'];
        
        Session::put('pos_cart', $cart);
        
        return response()->json([
            'success' => true,
            'message' => 'Quantidade atualizada',
            'cart' => $cart,
            'cartTotal' => $this->calculateCartTotal($cart)
        ]);
    }
    
    /**
     * Remove item do carrinho.
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer'
        ]);
        
        $cart = Session::get('pos_cart', []);
        $productId = $request->product_id;
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('pos_cart', $cart);
            
            return response()->json([
                'success' => true,
                'message' => 'Item removido do carrinho',
                'cart' => $cart,
                'cartTotal' => $this->calculateCartTotal($cart)
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Item não encontrado no carrinho'
        ]);
    }
    
    /**
     * Finaliza a venda e limpa o carrinho.
     */
    public function finalizeSale(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|string|in:dinheiro,cartao_credito,cartao_debito,pix,boleto',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            // 1. Validar estoque suficiente para todos os produtos
            $stockErrors = [];
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if (!$product) {
                    throw new \Exception("Produto não encontrado: ID {$item['product_id']}");
                }
                
                if ($product->stock < $item['quantity']) {
                    $stockErrors[] = "Estoque insuficiente para {$product->name}. Disponível: {$product->stock}, Solicitado: {$item['quantity']}";
                }
            }
            
            if (!empty($stockErrors)) {
                throw new \Exception(implode('; ', $stockErrors));
            }
            
            // 2. Calcular total da venda
            $total = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });
            
            // 3. Gerar número interno da venda
            $saleNumberService = new SaleNumberService();
            $saleNumber = $saleNumberService->generateNextNumber();
            
            // 4. Criar a venda com status inicial authorized_pending
            $sale = Sale::create([
                'number' => $saleNumber,
                'customer_id' => $request->customer_id,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'status' => 'authorized_pending' // Status inicial antes da autorização SEFAZ
            ]);
            
            // 5. Adicionar itens da venda e debitar estoque
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                // Criar item da venda
                $sale->saleItems()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['quantity'] * $item['price']
                ]);
                
                // Debitar estoque do produto
                $product->decrement('stock', $item['quantity']);
                
                Log::info('Estoque debitado', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_sold' => $item['quantity'],
                    'stock_remaining' => $product->fresh()->stock
                ]);
            }
            
            DB::commit();
            
            // Limpar carrinho da sessão
            session()->forget('pos_cart');
            
            Log::info('Venda finalizada com sucesso', [
                'sale_id' => $sale->id,
                'sale_number' => $saleNumber,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'customer_id' => $request->customer_id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Venda finalizada com sucesso!',
                'sale_number' => $saleNumber,
                'sale_id' => $sale->id,
                'total' => $total
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao finalizar venda', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar venda: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Limpa o carrinho.
     */
    public function clearCart()
    {
        Session::forget('pos_cart');
        
        return response()->json([
            'success' => true,
            'message' => 'Carrinho limpo com sucesso'
        ]);
    }
    
    /**
     * Calcula o total do carrinho.
     */
    private function calculateCartTotal(array $cart): float
    {
        return array_sum(array_column($cart, 'subtotal'));
    }
}