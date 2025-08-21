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
        'password',
    ];

    /**
     * Get the company config that owns the certificate.
     */
    public function companyConfig(): BelongsTo
    {
        return $this->belongsTo(CompanyConfig::class);
    }
}
