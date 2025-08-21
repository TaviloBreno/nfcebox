<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyConfig extends Model
{
    use HasFactory;
    protected $fillable = [
        'cnpj',
        'ie',
        'im',
        'corporate_name',
        'trade_name',
        'address_json',
        'environment',
        'nfce_series',
        'nfce_number',
        'csc_id',
        'csc_token',
    ];

    protected $casts = [
        'address_json' => 'array',
        'nfce_series' => 'integer',
        'nfce_number' => 'integer',
    ];

    /**
     * Get the certificates for the company config.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
