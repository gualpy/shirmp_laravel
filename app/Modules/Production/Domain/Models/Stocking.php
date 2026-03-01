<?php

namespace App\Modules\Production\Domain\Models;

use App\Models\Tenant;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stocking extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'stocked_at',
        'pl_qty',
        'hatchery_code',
        'batch_code',
        'initial_pp_grams',
        'density_pl_m2',
        'density_pl_ha',
    ];

    protected function casts(): array
    {
        return [
            'stocked_at' => 'date',
            'initial_pp_grams' => 'decimal:2',
            'density_pl_m2' => 'decimal:4',
            'density_pl_ha' => 'decimal:2',
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
