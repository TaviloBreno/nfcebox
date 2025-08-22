<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'company_config_id',
        'alias',
        'path',
        'file_path',
        'password',
        'subject',
        'issuer',
        'expires_at',
        'is_valid',
        'is_default',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_valid' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the company config that owns the certificate.
     */
    public function companyConfig(): BelongsTo
    {
        return $this->belongsTo(CompanyConfig::class);
    }
}
