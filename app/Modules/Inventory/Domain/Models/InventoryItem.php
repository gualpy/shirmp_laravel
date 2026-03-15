<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Models\Tenant;
use App\Modules\Inventory\Domain\Enums\InventoryCategory;
use App\Modules\Inventory\Domain\Enums\InventoryUnit;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'category',
        'name',
        'unit',
        'current_stock',
        'min_stock',
        'cost_per_unit',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'category' => InventoryCategory::class,
            'unit' => InventoryUnit::class,
            'current_stock' => 'decimal:2',
            'min_stock' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
            'is_active' => 'bool',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
