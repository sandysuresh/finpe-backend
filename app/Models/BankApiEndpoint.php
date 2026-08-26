<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankApiEndpoint extends Model
{
    protected $fillable = [
        'bank_id', 'name', 'slug', 'method', 'bank_path', 'description',
        'request_params', 'response_params', 'sample_request', 'sample_response',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'request_params' => 'array',
            'response_params' => 'array',
            'sample_request' => 'array',
            'sample_response' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
