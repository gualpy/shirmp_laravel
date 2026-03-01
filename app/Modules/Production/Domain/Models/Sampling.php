<?php

namespace App\Modules\Production\Domain\Models;

use App\Models\Tenant;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sampling extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'sampled_at',
        'pp_grams',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sampled_at' => 'date',
            'pp_grams' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }
}
