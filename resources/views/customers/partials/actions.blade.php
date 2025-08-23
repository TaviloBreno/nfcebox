<div class="btn-group" role="group">
    <a href="{{ route('customers.show', $customer) }}" 
       class="btn btn-sm btn-outline-info" 
       title="Visualizar">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('customers.edit', $customer) }}" 
       class="btn btn-sm btn-outline-primary" 
       title="Editar">
        <i class="fas fa-edit"></i>
    </a>
    <button type="button" 
            class="btn btn-sm btn-outline-danger" 
            title="Excluir" 
            onclick="confirmDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}');">
        <i class="fas fa-trash"></i>
    </button>
</div>