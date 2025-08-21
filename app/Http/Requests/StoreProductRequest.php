<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove máscaras dos campos numéricos se necessário
        $data = $this->all();
        
        if (isset($data['ncm'])) {
            $data['ncm'] = preg_replace('/[^0-9]/', '', $data['ncm']);
        }
        
        if (isset($data['cfop'])) {
            $data['cfop'] = preg_replace('/[^0-9]/', '', $data['cfop']);
        }
        
        if (isset($data['cest'])) {
            $data['cest'] = preg_replace('/[^0-9]/', '', $data['cest']);
        }
        
        $this->merge($data);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:10'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:99999.999'],
            'ncm' => [
                'required',
                'string',
                'size:8',
                'regex:/^[0-9]{8}$/'
            ],
            'cfop' => [
                'required',
                'string',
                'size:4',
                'regex:/^[0-9]{4}$/'
            ],
            'cest' => [
                'nullable',
                'string',
                'size:7',
                'regex:/^[0-9]{7}$/'
            ],
            'icms_origin' => ['required', 'integer', 'between:0,8'],
            'icms_cst' => ['required', 'string', 'size:3', 'regex:/^[0-9]{3}$/'],
            'icms_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ipi_cst' => ['nullable', 'string', 'size:2', 'regex:/^[0-9]{2}$/'],
            'ipi_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pis_cst' => ['nullable', 'string', 'size:2', 'regex:/^[0-9]{2}$/'],
            'pis_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cofins_cst' => ['nullable', 'string', 'size:2', 'regex:/^[0-9]{2}$/'],
            'cofins_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O campo nome deve ser um texto.',
            'name.max' => 'O campo nome não pode ter mais de 255 caracteres.',
            
            'description.string' => 'O campo descrição deve ser um texto.',
            'description.max' => 'O campo descrição não pode ter mais de 1000 caracteres.',
            
            'sku.required' => 'O campo SKU é obrigatório.',
            'sku.string' => 'O campo SKU deve ser um texto.',
            'sku.max' => 'O campo SKU não pode ter mais de 50 caracteres.',
            'sku.unique' => 'Este SKU já está sendo usado por outro produto.',
            
            'barcode.string' => 'O campo código de barras deve ser um texto.',
            'barcode.max' => 'O campo código de barras não pode ter mais de 50 caracteres.',
            
            'price.required' => 'O campo preço é obrigatório.',
            'price.numeric' => 'O campo preço deve ser um número.',
            'price.min' => 'O campo preço deve ser maior ou igual a zero.',
            'price.max' => 'O campo preço não pode ser maior que 999.999,99.',
            
            'cost_price.numeric' => 'O campo preço de custo deve ser um número.',
            'cost_price.min' => 'O campo preço de custo deve ser maior ou igual a zero.',
            'cost_price.max' => 'O campo preço de custo não pode ser maior que 999.999,99.',
            
            'stock_quantity.required' => 'O campo quantidade em estoque é obrigatório.',
            'stock_quantity.integer' => 'O campo quantidade em estoque deve ser um número inteiro.',
            'stock_quantity.min' => 'O campo quantidade em estoque deve ser maior ou igual a zero.',
            
            'min_stock.integer' => 'O campo estoque mínimo deve ser um número inteiro.',
            'min_stock.min' => 'O campo estoque mínimo deve ser maior ou igual a zero.',
            
            'unit.required' => 'O campo unidade é obrigatório.',
            'unit.string' => 'O campo unidade deve ser um texto.',
            'unit.max' => 'O campo unidade não pode ter mais de 10 caracteres.',
            
            'weight.numeric' => 'O campo peso deve ser um número.',
            'weight.min' => 'O campo peso deve ser maior ou igual a zero.',
            'weight.max' => 'O campo peso não pode ser maior que 99.999,999.',
            
            'ncm.required' => 'O campo NCM é obrigatório.',
            'ncm.string' => 'O campo NCM deve ser um texto.',
            'ncm.size' => 'O campo NCM deve ter exatamente 8 dígitos.',
            'ncm.regex' => 'O campo NCM deve conter apenas números.',
            
            'cfop.required' => 'O campo CFOP é obrigatório.',
            'cfop.string' => 'O campo CFOP deve ser um texto.',
            'cfop.size' => 'O campo CFOP deve ter exatamente 4 dígitos.',
            'cfop.regex' => 'O campo CFOP deve conter apenas números.',
            
            'cest.string' => 'O campo CEST deve ser um texto.',
            'cest.size' => 'O campo CEST deve ter exatamente 7 dígitos.',
            'cest.regex' => 'O campo CEST deve conter apenas números.',
            
            'icms_origin.required' => 'O campo origem do ICMS é obrigatório.',
            'icms_origin.integer' => 'O campo origem do ICMS deve ser um número inteiro.',
            'icms_origin.between' => 'O campo origem do ICMS deve estar entre 0 e 8.',
            
            'icms_cst.required' => 'O campo CST do ICMS é obrigatório.',
            'icms_cst.string' => 'O campo CST do ICMS deve ser um texto.',
            'icms_cst.size' => 'O campo CST do ICMS deve ter exatamente 3 dígitos.',
            'icms_cst.regex' => 'O campo CST do ICMS deve conter apenas números.',
            
            'icms_rate.numeric' => 'O campo alíquota do ICMS deve ser um número.',
            'icms_rate.min' => 'O campo alíquota do ICMS deve ser maior ou igual a zero.',
            'icms_rate.max' => 'O campo alíquota do ICMS não pode ser maior que 100.',
            
            'ipi_cst.string' => 'O campo CST do IPI deve ser um texto.',
            'ipi_cst.size' => 'O campo CST do IPI deve ter exatamente 2 dígitos.',
            'ipi_cst.regex' => 'O campo CST do IPI deve conter apenas números.',
            
            'ipi_rate.numeric' => 'O campo alíquota do IPI deve ser um número.',
            'ipi_rate.min' => 'O campo alíquota do IPI deve ser maior ou igual a zero.',
            'ipi_rate.max' => 'O campo alíquota do IPI não pode ser maior que 100.',
            
            'pis_cst.string' => 'O campo CST do PIS deve ser um texto.',
            'pis_cst.size' => 'O campo CST do PIS deve ter exatamente 2 dígitos.',
            'pis_cst.regex' => 'O campo CST do PIS deve conter apenas números.',
            
            'pis_rate.numeric' => 'O campo alíquota do PIS deve ser um número.',
            'pis_rate.min' => 'O campo alíquota do PIS deve ser maior ou igual a zero.',
            'pis_rate.max' => 'O campo alíquota do PIS não pode ser maior que 100.',
            
            'cofins_cst.string' => 'O campo CST do COFINS deve ser um texto.',
            'cofins_cst.size' => 'O campo CST do COFINS deve ter exatamente 2 dígitos.',
            'cofins_cst.regex' => 'O campo CST do COFINS deve conter apenas números.',
            
            'cofins_rate.numeric' => 'O campo alíquota do COFINS deve ser um número.',
            'cofins_rate.min' => 'O campo alíquota do COFINS deve ser maior ou igual a zero.',
            'cofins_rate.max' => 'O campo alíquota do COFINS não pode ser maior que 100.',
            
            'is_active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'description' => 'descrição',
            'sku' => 'SKU',
            'barcode' => 'código de barras',
            'price' => 'preço',
            'cost_price' => 'preço de custo',
            'stock_quantity' => 'quantidade em estoque',
            'min_stock' => 'estoque mínimo',
            'unit' => 'unidade',
            'weight' => 'peso',
            'ncm' => 'NCM',
            'cfop' => 'CFOP',
            'cest' => 'CEST',
            'icms_origin' => 'origem do ICMS',
            'icms_cst' => 'CST do ICMS',
            'icms_rate' => 'alíquota do ICMS',
            'ipi_cst' => 'CST do IPI',
            'ipi_rate' => 'alíquota do IPI',
            'pis_cst' => 'CST do PIS',
            'pis_rate' => 'alíquota do PIS',
            'cofins_cst' => 'CST do COFINS',
            'cofins_rate' => 'alíquota do COFINS',
            'is_active' => 'ativo',
        ];
    }
}