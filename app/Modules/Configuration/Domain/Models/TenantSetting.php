<?php

namespace App\Modules\Configuration\Domain\Models;

use App\Models\Tenant;
use App\Modules\Configuration\Domain\Enums\FeedingStrategy;
use App\Modules\Configuration\Domain\Enums\UnitSystem;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSetting extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'feeding_strategy',
        'feeding_pct_small',
        'feeding_pct_medium',
        'feeding_pct_large',
        'allow_post_close_adjustments',
        'unit_system',
        'decimals_precision',
    ];

    protected function casts(): array
    {
        return [
            'feeding_strategy' => FeedingStrategy::class,
            'feeding_pct_small' => 'decimal:2',
            'feeding_pct_medium' => 'decimal:2',
            'feeding_pct_large' => 'decimal:2',
            'allow_post_close_adjustments' => 'bool',
            'unit_system' => UnitSystem::class,
            'decimals_precision' => 'int',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
