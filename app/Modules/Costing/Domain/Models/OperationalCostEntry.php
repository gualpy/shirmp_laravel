<?php

namespace App\Modules\Costing\Domain\Models;

use App\Models\Tenant;
use App\Modules\Costing\Domain\Enums\OperationalCostType;
use App\Modules\Production\Domain\Models\Cycle;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalCostEntry extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'cost_type',
        'amount',
        'occurred_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cost_type' => OperationalCostType::class,
            'amount' => 'decimal:2',
            'occurred_at' => 'date',
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
