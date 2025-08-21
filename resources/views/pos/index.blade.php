@extends('layouts.app')

@section('title', 'PDV - Ponto de Venda')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
                <h1 class="h4 h3-md mb-0">PDV - Ponto de Venda</h1>
                <button type="button" class="btn btn-outline-danger btn-sm d-md-none" onclick="clearCart()" title="Limpar Carrinho">
                    <i class="fas fa-trash"></i>
                </button>
                <button type="button" class="btn btn-outline-danger d-none d-md-inline-block" onclick="clearCart()">
                    <i class="fas fa-trash"></i> Limpar Carrinho
                </button>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Coluna da Esquerda - Seleção de Cliente e Produtos -->
        <div class="col-lg-8 order-2 order-lg-1">
            <!-- Card de Seleção de Cliente -->
            <div class="card mb-3 mb-md-4">
                <div class="card-header py-2 py-md-3">
                    <h6 class="card-title mb-0 h6-mobile">
                        <i class="fas fa-user"></i> Cliente
                    </h6>
                </div>
                <div class="card-body py-2 py-md-3">
                    <div class="row">
                        <div class="col-12">
                            <label for="customer_search" class="form-label form-label-sm d-none d-md-block">Selecionar Cliente</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="customer_search" 
                                       placeholder="Nome ou documento do cliente..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearCustomer()" title="Limpar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <input type="hidden" id="customer_id" name="customer_id" value="">
                            <div id="customer_results" class="mt-2" style="display: none;"></div>
                            <div class="mt-2">
                                <small class="text-muted small-mobile" id="selected_customer">
                                    <i class="fas fa-user-slash"></i> Consumidor não identificado
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card de Busca de Produtos -->
            <div class="card mb-3 mb-md-4">
                <div class="card-header py-2 py-md-3">
                    <h6 class="card-title mb-0 h6-mobile">
                        <i class="fas fa-search"></i> Buscar Produtos
                    </h6>
                </div>
                <div class="card-body py-2 py-md-3">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="product_search" 
                               placeholder="Nome ou SKU do produto..." autocomplete="off">
                        <button class="btn btn-primary btn-sm d-md-none" type="button" onclick="openProductModal()" title="Buscar">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-primary d-none d-md-inline-block" type="button" onclick="openProductModal()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    <div id="search_results" class="mt-2 mt-md-3" style="display: none;"></div>
                </div>
            </div>
        </div>
        
        <!-- Coluna da Direita - Carrinho -->
        <div class="col-lg-4 order-1 order-lg-2 mb-3 mb-lg-0">
            <div class="card cart-card">
                <div class="card-header py-2 py-md-3">
                    <h6 class="card-title mb-0 h6-mobile">
                        <i class="fas fa-shopping-cart"></i> Carrinho
                        <span class="badge bg-primary ms-2" id="cart_count">{{ count($cart) }}</span>
                    </h6>
                </div>
                <div class="card-body cart-body py-2 py-md-3">
                    <div id="cart_items">
                        @if(empty($cart))
                            <div class="text-center text-muted py-4" id="empty_cart">
                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                <p>Carrinho vazio</p>
                            </div>
                        @else
                            @foreach($cart as $item)
                                <div class="cart-item mb-3 p-3 border rounded" data-product-id="{{ $item['id'] }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $item['name'] }}</h6>
                                            <small class="text-muted">SKU: {{ $item['sku'] }}</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="removeFromCart({{ $item['id'] }})">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="row align-items-center g-2">
                                        <div class="col-4">
                                            <small class="text-muted small-mobile">Qtd:</small>
                                            <input type="number" class="form-control form-control-sm" 
                                                   value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock'] }}"
                                                   onchange="updateCartQuantity({{ $item['id'] }}, this.value)">
                                        </div>
                                        <div class="col-4 text-center">
                                            <small class="text-muted small-mobile">Preço:</small><br>
                                            <strong class="price-mobile">R$ {{ number_format($item['price'], 2, ',', '.') }}</strong>
                                        </div>
                                        <div class="col-4 text-end">
                                            <small class="text-muted small-mobile">Subtotal:</small><br>
                                            <strong class="text-primary price-mobile">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                
                <!-- Total e Forma de Pagamento -->
                <div class="card-footer py-2 py-md-3">
                    <div class="mb-2 mb-md-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 h6-mobile">Total:</h6>
                            <h5 class="mb-0 text-success total-mobile" id="cart_total">
                                R$ {{ number_format($cartTotal, 2, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                    
                    <div class="mb-2 mb-md-3">
                        <label for="payment_method" class="form-label form-label-sm">Forma de Pagamento</label>
                        <select class="form-select form-select-sm" id="payment_method" name="payment_method" required>
                            <option value="">Selecione...</option>
                            <option value="money">💵 Dinheiro</option>
                            <option value="pix">📱 PIX</option>
                            <option value="debit_card">💳 Cartão de Débito</option>
                            <option value="credit_card">💳 Cartão de Crédito</option>
                            <option value="bank_transfer">🏦 Transferência Bancária</option>
                            <option value="check">📝 Cheque</option>
                        </select>
                    </div>
                    
                    <button type="button" class="btn btn-success w-100 finalize-btn" 
                            onclick="finalizeSale()" {{ empty($cart) ? 'disabled' : '' }} id="finalize-btn">
                        <i class="fas fa-check"></i> <span class="d-none d-sm-inline">Finalizar Venda</span><span class="d-sm-none">Finalizar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Busca de Produtos -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Produtos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="modal_product_search" 
                           placeholder="Digite o nome ou SKU do produto...">
                    <button class="btn btn-primary" type="button" onclick="searchProducts()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div id="modal_search_results">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-3"></i>
                        <p>Digite pelo menos 2 caracteres para buscar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Venda -->
<div class="modal fade" id="saleConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Venda Finalizada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="sale_success_content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-plus"></i> Nova Venda
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Estilos responsivos específicos para PDV */

/* Mobile First - Estilos base para mobile */
.h6-mobile {
    font-size: 0.9rem;
    font-weight: 600;
}

.small-mobile {
    font-size: 0.75rem;
}

.price-mobile {
    font-size: 0.85rem;
}

.total-mobile {
    font-size: 1.1rem;
}

.form-label-sm {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

.finalize-btn {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

/* Carrinho responsivo */
.cart-card {
    position: relative;
}

.cart-body {
    max-height: 300px;
    overflow-y: auto;
}

/* Ajustes para itens do carrinho */
.cart-item {
    padding: 0.75rem !important;
    margin-bottom: 0.75rem !important;
}

.cart-item h6 {
    font-size: 0.85rem;
    line-height: 1.2;
}

.cart-item small {
    font-size: 0.7rem;
}

/* Tablet adjustments */
@media (min-width: 576px) {
    .h6-mobile {
        font-size: 1rem;
    }
    
    .small-mobile {
        font-size: 0.8rem;
    }
    
    .price-mobile {
        font-size: 0.9rem;
    }
    
    .total-mobile {
        font-size: 1.25rem;
    }
    
    .finalize-btn {
        font-size: 1rem;
        padding: 0.6rem 1rem;
    }
    
    .cart-body {
        max-height: 350px;
    }
}

/* Tablet landscape and small desktop */
@media (min-width: 768px) {
    .h3-md {
        font-size: 1.75rem;
    }
    
    .cart-card {
        position: sticky;
        top: 1rem;
    }
    
    .cart-body {
        max-height: 400px;
    }
    
    .finalize-btn {
        font-size: 1.1rem;
        padding: 0.75rem 1rem;
    }
}

/* Desktop */
@media (min-width: 992px) {
    .cart-body {
        max-height: 450px;
    }
    
    .finalize-btn {
        font-size: 1.125rem;
        padding: 0.875rem 1rem;
    }
}

/* Large desktop */
@media (min-width: 1200px) {
    .cart-body {
        max-height: 500px;
    }
}

/* Melhorias para touch devices */
@media (hover: none) and (pointer: coarse) {
    .btn {
        min-height: 44px;
        min-width: 44px;
    }
    
    .form-control, .form-select {
        min-height: 44px;
    }
    
    .input-group-sm .form-control,
    .input-group-sm .btn {
        min-height: 38px;
    }
    
    .form-control-sm {
        min-height: 32px;
    }
}

/* Ajustes para orientação landscape em mobile */
@media (max-width: 768px) and (orientation: landscape) {
    .cart-body {
        max-height: 200px;
    }
    
    .card-body {
        padding: 0.5rem;
    }
    
    .card-header {
        padding: 0.5rem;
    }
    
    .card-footer {
        padding: 0.5rem;
    }
}

/* Melhorias para acessibilidade */
.btn:focus,
.form-control:focus,
.form-select:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    border-color: #86b7fe;
}

/* Animações suaves */
.card,
.btn,
.form-control,
.form-select {
    transition: all 0.15s ease-in-out;
}

/* Scroll suave para o carrinho */
.cart-body {
    scroll-behavior: smooth;
}

.cart-body::-webkit-scrollbar {
    width: 6px;
}

.cart-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.cart-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.cart-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endpush

@section('scripts')
<script>
// Variáveis globais
let searchTimeout;
let customerSearchTimeout;
const productModal = new bootstrap.Modal(document.getElementById('productModal'));
const saleConfirmModal = new bootstrap.Modal(document.getElementById('saleConfirmModal'));

// Busca de clientes em tempo real
document.getElementById('customer_search').addEventListener('input', function() {
    const query = this.value;
    
    if (query.length >= 2) {
        clearTimeout(customerSearchTimeout);
        customerSearchTimeout = setTimeout(() => {
            searchCustomersInline(query);
        }, 300);
    } else {
        document.getElementById('customer_results').style.display = 'none';
    }
});

// Busca de produtos em tempo real
document.getElementById('product_search').addEventListener('input', function() {
    const query = this.value;
    
    if (query.length >= 2) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchProductsInline(query);
        }, 300);
    } else {
        document.getElementById('search_results').style.display = 'none';
    }
});

// Busca clientes inline
function searchCustomersInline(query) {
    fetch(`{{ route('pos.search.customers') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('customer_results');
            
            if (data.length === 0) {
                resultsDiv.innerHTML = '<div class="alert alert-info alert-sm">Nenhum cliente encontrado</div>';
            } else {
                let html = '<div class="list-group list-group-flush border rounded">';
                data.forEach(customer => {
                    html += `
                        <button type="button" class="list-group-item list-group-item-action" 
                                onclick="selectCustomer(${customer.id}, '${customer.name}', '${customer.document || ''}')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${customer.name}</h6>
                                    <small class="text-muted">${customer.document || 'Sem documento'}</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </button>
                    `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
            }
            
            resultsDiv.style.display = 'block';
        })
        .catch(error => {
            console.error('Erro na busca de clientes:', error);
            showAlert('Erro ao buscar clientes', 'danger');
        });
}

// Seleciona um cliente
function selectCustomer(customerId, customerName, customerDocument) {
    document.getElementById('customer_id').value = customerId;
    document.getElementById('customer_search').value = customerName;
    document.getElementById('customer_results').style.display = 'none';
    
    const selectedCustomerDiv = document.getElementById('selected_customer');
    selectedCustomerDiv.innerHTML = `
        <i class="fas fa-user text-success"></i> 
        <strong>${customerName}</strong>
        ${customerDocument ? `<span class="text-muted"> - ${customerDocument}</span>` : ''}
    `;
}

// Limpa seleção de cliente
function clearCustomer() {
    document.getElementById('customer_id').value = '';
    document.getElementById('customer_search').value = '';
    document.getElementById('customer_results').style.display = 'none';
    
    const selectedCustomerDiv = document.getElementById('selected_customer');
    selectedCustomerDiv.innerHTML = '<i class="fas fa-user-slash"></i> Consumidor não identificado';
}

// Busca produtos inline
function searchProductsInline(query) {
    fetch(`{{ route('pos.search.products') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('search_results');
            
            if (data.length === 0) {
                resultsDiv.innerHTML = '<div class="alert alert-info">Nenhum produto encontrado</div>';
            } else {
                let html = '<div class="list-group">';
                data.forEach(product => {
                    html += `
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${product.name}</h6>
                                <small class="text-muted">SKU: ${product.sku} | Estoque: ${product.stock}</small>
                            </div>
                            <div class="text-end">
                                <div class="mb-2">
                                    <strong class="text-success">R$ ${parseFloat(product.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong>
                                </div>
                                <button class="btn btn-sm btn-primary" onclick="addProductToCart(${product.id}, 1)">
                                    <i class="fas fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
            }
            
            resultsDiv.style.display = 'block';
        })
        .catch(error => {
            console.error('Erro na busca:', error);
            showAlert('Erro ao buscar produtos', 'danger');
        });
}

// Abre modal de produtos
function openProductModal() {
    productModal.show();
    document.getElementById('modal_product_search').focus();
}

// Busca produtos no modal
function searchProducts() {
    const query = document.getElementById('modal_product_search').value;
    
    if (query.length < 2) {
        showAlert('Digite pelo menos 2 caracteres', 'warning');
        return;
    }
    
    fetch(`{{ route('pos.search.products') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('modal_search_results');
            
            if (data.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>Nenhum produto encontrado para "${query}"</p>
                    </div>
                `;
            } else {
                let html = '<div class="row">';
                data.forEach(product => {
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">${product.name}</h6>
                                    <p class="card-text">
                                        <small class="text-muted">SKU: ${product.sku}</small><br>
                                        <small class="text-muted">Estoque: ${product.stock}</small><br>
                                        <strong class="text-success">R$ ${parseFloat(product.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong>
                                    </p>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="qty_${product.id}" value="1" min="1" max="${product.stock}">
                                        <button class="btn btn-primary" onclick="addProductToCart(${product.id}, document.getElementById('qty_${product.id}').value)">
                                            <i class="fas fa-plus"></i> Adicionar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Erro na busca:', error);
            showAlert('Erro ao buscar produtos', 'danger');
        });
}

// Adiciona produto ao carrinho
function addProductToCart(productId, quantity) {
    const data = {
        product_id: productId,
        quantity: parseInt(quantity)
    };
    
    fetch('{{ route("pos.cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart, data.cartTotal);
            showAlert(data.message, 'success');
            productModal.hide();
            document.getElementById('product_search').value = '';
            document.getElementById('search_results').style.display = 'none';
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showAlert('Erro ao adicionar produto', 'danger');
    });
}

// Função auxiliar para adicionar produto ao carrinho (compatibilidade)
function addToCart(productId, productName, productPrice, productStock, productUnit = 'UN') {
    // Verificar se o produto já está no carrinho
    let existingItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    
    if (existingItem) {
        // Se já existe, incrementar quantidade
        let qtyInput = existingItem.querySelector('.form-control');
        let currentQty = parseInt(qtyInput.value);
        let newQty = currentQty + 1;
        
        if (newQty <= productStock) {
            updateCartQuantity(productId, newQty);
        } else {
            showAlert('Quantidade não pode exceder o estoque disponível!', 'warning');
        }
    } else {
        // Adicionar novo item ao carrinho
        addProductToCart(productId, 1);
    }
}

// Atualiza quantidade no carrinho
function updateCartQuantity(productId, quantity) {
    const data = {
        product_id: productId,
        quantity: parseInt(quantity)
    };
    
    fetch('{{ route("pos.cart.update") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart, data.cartTotal);
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'danger');
            // Reverte o valor se houver erro
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showAlert('Erro ao atualizar quantidade', 'danger');
    });
}

// Remove item do carrinho
function removeFromCart(productId) {
    if (!confirm('Deseja remover este item do carrinho?')) {
        return;
    }
    
    const data = {
        product_id: productId
    };
    
    fetch('{{ route("pos.cart.remove") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart, data.cartTotal);
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showAlert('Erro ao remover item', 'danger');
    });
}

// Limpa carrinho
function clearCart() {
    if (!confirm('Deseja limpar todo o carrinho?')) {
        return;
    }
    
    fetch('{{ route("pos.cart.clear") }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showAlert('Erro ao limpar carrinho', 'danger');
    });
}

// Finaliza venda
function finalizeSale() {
    const customerId = document.getElementById('customer_id').value;
    const paymentMethod = document.getElementById('payment_method').value;
    
    if (!paymentMethod) {
        showAlert('Selecione a forma de pagamento', 'warning');
        return;
    }
    
    // Verificar se há itens no carrinho
    const cartItems = document.querySelectorAll('.cart-item');
    if (cartItems.length === 0) {
        showAlert('Adicione pelo menos um produto ao carrinho!', 'warning');
        return;
    }
    
    // Confirmar venda
    if (!confirm('Confirma a finalização da venda?')) {
        return;
    }
    
    const data = {
        customer_id: customerId || null,
        payment_method: paymentMethod
    };
    
    fetch('{{ route("pos.finalize") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const sale = data.sale;
            document.getElementById('sale_success_content').innerHTML = `
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <h4>Venda Finalizada com Sucesso!</h4>
                </div>
                <div class="row">
                    <div class="col-6">
                        <strong>Número:</strong><br>
                        <span class="text-primary">${sale.number}</span>
                    </div>
                    <div class="col-6">
                        <strong>Total:</strong><br>
                        <span class="text-success">R$ ${parseFloat(sale.total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                    </div>
                </div>
                <div class="mt-3">
                    <strong>Forma de Pagamento:</strong><br>
                    <span class="badge bg-info">${getPaymentMethodLabel(sale.payment_method)}</span>
                </div>
            `;
            saleConfirmModal.show();
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showAlert('Erro ao finalizar venda', 'danger');
    });
}

// Atualiza display do carrinho
function updateCartDisplay(cart, cartTotal) {
    const cartItemsDiv = document.getElementById('cart_items');
    const cartCountSpan = document.getElementById('cart_count');
    const cartTotalSpan = document.getElementById('cart_total');
    
    // Atualiza contador
    cartCountSpan.textContent = Object.keys(cart).length;
    
    // Atualiza total
    cartTotalSpan.textContent = `R$ ${parseFloat(cartTotal).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
    
    // Atualiza itens
    if (Object.keys(cart).length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="text-center text-muted py-4" id="empty_cart">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <p>Carrinho vazio</p>
            </div>
        `;
        document.querySelector('button[onclick="finalizeSale()"]').disabled = true;
    } else {
        let html = '';
        Object.values(cart).forEach(item => {
            html += `
                <div class="cart-item mb-3 p-3 border rounded" data-product-id="${item.id}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">SKU: ${item.sku}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="removeFromCart(${item.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-4">
                            <small class="text-muted">Qtd:</small>
                            <input type="number" class="form-control form-control-sm" 
                                   value="${item.quantity}" min="1" max="${item.stock}"
                                   onchange="updateCartQuantity(${item.id}, this.value)">
                        </div>
                        <div class="col-4 text-center">
                            <small class="text-muted">Preço:</small><br>
                            <strong>R$ ${parseFloat(item.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong>
                        </div>
                        <div class="col-4 text-end">
                            <small class="text-muted">Subtotal:</small><br>
                            <strong class="text-primary">R$ ${parseFloat(item.subtotal).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong>
                        </div>
                    </div>
                </div>
            `;
        });
        cartItemsDiv.innerHTML = html;
        document.querySelector('button[onclick="finalizeSale()"]').disabled = false;
    }
}

// Função auxiliar para labels de forma de pagamento
function getPaymentMethodLabel(method) {
    const labels = {
        'cash': 'Dinheiro',
        'pix': 'PIX',
        'card': 'Cartão',
        'credit_card': 'Cartão de Crédito',
        'debit_card': 'Cartão de Débito'
    };
    return labels[method] || method;
}

// Função para mostrar alertas
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Remove automaticamente após 5 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Event listeners para modal
document.getElementById('modal_product_search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchProducts();
    }
});

document.getElementById('product_search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        openProductModal();
    }
});
</script>
@endsection