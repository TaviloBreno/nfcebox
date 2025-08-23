@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Clientes</h1>
                <x-button 
                    href="{{ route('customers.create') }}" 
                    variant="primary" 
                    icon="fas fa-plus">
                    Novo Cliente
                </x-button>
            </div>

            @if(session('success'))
                <x-alert type="success" dismissible>
                    {{ session('success') }}
                </x-alert>
            @endif

            @if(session('error'))
                <x-alert type="danger" dismissible>
                    {{ session('error') }}
                </x-alert>
            @endif

            <x-card>
                <x-slot name="header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0">Lista de Clientes</h5>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('customers.index') }}" class="d-flex">
                                <x-form-input 
                                    name="search" 
                                    placeholder="Buscar por nome, CPF/CNPJ ou email..." 
                                    value="{{ $search }}" 
                                    class="me-2" />
                                <x-button 
                                    type="submit" 
                                    variant="outline-secondary" 
                                    class="ms-2" 
                                    icon="fas fa-search" />
                                @if($search)
                                    <x-button 
                                        href="{{ route('customers.index') }}" 
                                        variant="outline-danger" 
                                        class="ms-1" 
                                        icon="fas fa-times" />
                                @endif
                            </form>
                        </div>
                    </div>
                </x-slot>

                @php
                    // Monte as linhas ANTES e passe para o componente
                    $rows = $customers->map(function ($customer) {
                        // Normaliza o address (pode ser null, array ou json)
                        $addr = $customer->address;
                        if (is_string($addr)) {
                            // tenta decodificar JSON armazenado como string
                            $decoded = json_decode($addr, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $addr = $decoded;
                            } else {
                                $addr = [];
                            }
                        } elseif (!is_array($addr)) {
                            $addr = [];
                        }

                        // Monta endereço completo com partes opcionais
                        $partsLine1 = array_filter([
                            $addr['street'] ?? null,
                            $addr['number'] ?? null,
                        ], fn($v) => filled($v));

                        $partsLine2 = array_filter([
                            $addr['complement'] ?? null,
                            $addr['neighborhood'] ?? null,
                        ], fn($v) => filled($v));

                        $cityState = trim(
                            trim(($addr['city'] ?? '') . '') .
                            (filled($addr['state'] ?? null) ? ' - ' . $addr['state'] : '')
                        );

                        $zipcode = $addr['zipcode'] ?? null;

                        $fullAddress = '';
                        if (!empty($partsLine1)) {
                            $fullAddress .= e(implode(', ', $partsLine1));
                        }
                        if (!empty($partsLine2)) {
                            $fullAddress .= (filled($fullAddress) ? ' - ' : '') . e(implode(' - ', $partsLine2));
                        }
                        if (filled($cityState)) {
                            $fullAddress .= (filled($fullAddress) ? ', ' : '') . e($cityState);
                        }
                        if (filled($zipcode)) {
                            $fullAddress .= (filled($fullAddress) ? ', ' : '') . 'CEP: ' . e($zipcode);
                        }
                        if ($fullAddress === '') {
                            $fullAddress = '-';
                        }

                        return [
                            'name'     => '<strong>' . e($customer->name) . '</strong>',
                            'document' => '<span class="font-monospace">' . e($customer->formatted_document) . '</span>',
                            'email'    => e($customer->email ?: '-'),
                            'phone'    => $customer->phone
                                ? '<span class="font-monospace">' . e($customer->formatted_phone) . '</span>'
                                : '<span class="text-muted">-</span>',
                            'address'  => $fullAddress,
                            'actions'  => view('customers.partials.actions', ['customer' => $customer])->render(),
                        ];
                    });
                @endphp

                @if($customers->count() > 0)
                    <x-data-table 
                        :headers="[
                            'name' => 'Nome',
                            'document' => 'CPF/CNPJ',
                            'email' => 'Email',
                            'phone' => 'Telefone',
                            // Agora mostra Endereço completo
                            'address' => 'Endereço',
                            'actions' => 'Ações'
                        ]"
                        :data="$rows"
                        striped
                        hover
                        responsive
                        searchable
                        sortable
                    />

                    <x-slot name="footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Mostrando {{ $customers->firstItem() }} a {{ $customers->lastItem() }} 
                                de {{ $customers->total() }} registros
                            </div>
                            {{ $customers->links() }}
                        </div>
                    </x-slot>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum cliente encontrado</h5>
                        @if($search)
                            <p class="text-muted">Tente ajustar os termos de busca</p>
                            <x-button 
                                href="{{ route('customers.index') }}" 
                                variant="outline-primary">
                                Ver todos os clientes
                            </x-button>
                        @else
                            <p class="text-muted">Comece criando seu primeiro cliente</p>
                            <x-button 
                                href="{{ route('customers.create') }}" 
                                variant="primary" 
                                icon="fas fa-plus">
                                Criar Cliente
                            </x-button>
                        @endif
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>

<x-modal id="deleteModal" title="Confirmar Exclusão">
    <p>Tem certeza que deseja excluir o cliente <strong id="customerName"></strong>?</p>
    <p class="text-muted">Esta ação não pode ser desfeita.</p>
    
    <x-slot name="footer">
        <x-button variant="secondary" data-bs-dismiss="modal">
            Cancelar
        </x-button>
        <form id="deleteForm" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger">
                Excluir
            </x-button>
        </form>
    </x-slot>
</x-modal>

@push('scripts')
<script>
function confirmDelete(customerId, customerName) {
    document.getElementById('customerName').textContent = customerName;
    document.getElementById('deleteForm').action = `/customers/${customerId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
@endsection
