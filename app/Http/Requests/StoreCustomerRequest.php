<?php

namespace App\Http\Requests;

use App\Traits\DocumentHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    use DocumentHelper;

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
        $this->merge($this->normalizeRequestData($this->all()));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'document' => [
                'required',
                'string',
                'unique:customers,document',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidDocument($value)) {
                        $fail('O campo documento deve ser um CPF ou CNPJ válido.');
                    }
                },
            ],
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:11',
                'regex:/^[0-9]+$/'
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip_code' => [
                'nullable',
                'string',
                'size:8',
                'regex:/^[0-9]{8}$/'
            ],
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
            
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O campo e-mail deve ser um endereço de e-mail válido.',
            'email.max' => 'O campo e-mail não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este e-mail já está sendo usado por outro cliente.',
            
            'document.required' => 'O campo documento é obrigatório.',
            'document.string' => 'O campo documento deve ser um texto.',
            'document.unique' => 'Este documento já está sendo usado por outro cliente.',
            
            'phone.required' => 'O campo telefone é obrigatório.',
            'phone.string' => 'O campo telefone deve ser um texto.',
            'phone.min' => 'O campo telefone deve ter pelo menos 10 dígitos.',
            'phone.max' => 'O campo telefone não pode ter mais de 11 dígitos.',
            'phone.regex' => 'O campo telefone deve conter apenas números.',
            
            'address.string' => 'O campo endereço deve ser um texto.',
            'address.max' => 'O campo endereço não pode ter mais de 500 caracteres.',
            
            'city.string' => 'O campo cidade deve ser um texto.',
            'city.max' => 'O campo cidade não pode ter mais de 100 caracteres.',
            
            'state.string' => 'O campo estado deve ser um texto.',
            'state.size' => 'O campo estado deve ter exatamente 2 caracteres.',
            
            'zip_code.string' => 'O campo CEP deve ser um texto.',
            'zip_code.size' => 'O campo CEP deve ter exatamente 8 dígitos.',
            'zip_code.regex' => 'O campo CEP deve conter apenas números.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'document' => 'documento',
            'phone' => 'telefone',
            'address' => 'endereço',
            'city' => 'cidade',
            'state' => 'estado',
            'zip_code' => 'CEP',
        ];
    }
}