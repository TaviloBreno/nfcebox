<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'document',
        'email',
        'phone',
        'address',
    ];

    protected $casts = [
        'address' => 'array',
    ];

    /**
     * Get the sales for the customer.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
    
    /**
     * Get formatted document (CPF/CNPJ)
     */
    public function getFormattedDocumentAttribute()
    {
        if (!$this->document) {
            return null;
        }
        
        $document = preg_replace('/\D/', '', $this->document);
        
        if (strlen($document) === 11) {
            // CPF: 000.000.000-00
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document);
        } elseif (strlen($document) === 14) {
            // CNPJ: 00.000.000/0000-00
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document);
        }
        
        return $this->document;
    }
    
    /**
     * Get formatted phone
     */
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) {
            return null;
        }
        
        $phone = preg_replace('/\D/', '', $this->phone);
        
        if (strlen($phone) === 11) {
            // Celular: (00) 00000-0000
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
        } elseif (strlen($phone) === 10) {
            // Fixo: (00) 0000-0000
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
        }
        
        return $this->phone;
    }
}
