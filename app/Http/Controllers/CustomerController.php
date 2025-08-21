<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('document', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            
            // Preparar dados do endereço como JSON
            $address = null;
            if ($request->filled(['street', 'number', 'neighborhood', 'city', 'state', 'zip_code'])) {
                $address = [
                    'street' => $request->street,
                    'number' => $request->number,
                    'complement' => $request->complement,
                    'neighborhood' => $request->neighborhood,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip_code' => $request->zip_code,
                ];
            }
            
            $data['address'] = $address;
            
            Customer::create($data);
            
            DB::commit();
            
            return redirect()->route('customers.index')
                ->with('success', 'Cliente criado com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            
            // Preparar dados do endereço como JSON
            $address = null;
            if ($request->filled(['street', 'number', 'neighborhood', 'city', 'state', 'zip_code'])) {
                $address = [
                    'street' => $request->street,
                    'number' => $request->number,
                    'complement' => $request->complement,
                    'neighborhood' => $request->neighborhood,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip_code' => $request->zip_code,
                ];
            }
            
            $data['address'] = $address;
            
            $customer->update($data);
            
            DB::commit();
            
            return redirect()->route('customers.index')
                ->with('success', 'Cliente atualizado com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            
            return redirect()->route('customers.index')
                ->with('success', 'Cliente excluído com sucesso!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao excluir cliente: ' . $e->getMessage());
        }
    }
}