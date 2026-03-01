<?php

namespace App\Modules\Configuration\Domain\Models;

use App\Models\Tenant;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedingGrowthTableRow extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'table_id',
        'day_from',
        'day_to',
        'pp_from_grams',
        'pp_to_grams',
        'feed_pct',
    ];

    protected function casts(): array
    {
        return [
            'day_from' => 'int',
            'day_to' => 'int',
            'pp_from_grams' => 'decimal:2',
            'pp_to_grams' => 'decimal:2',
            'feed_pct' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(FeedingGrowthTable::class, 'table_id');
    }
}
