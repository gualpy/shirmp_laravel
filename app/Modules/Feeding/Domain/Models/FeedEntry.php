<?php

namespace App\Modules\Feeding\Domain\Models;

use App\Models\Tenant;
use App\Modules\Production\Domain\Models\Cycle;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedEntry extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'feed_type_id',
        'fed_at',
        'amount_kg',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fed_at' => 'date',
            'amount_kg' => 'decimal:3',
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

    public function feedType(): BelongsTo
    {
        return $this->belongsTo(FeedType::class);
    }
}
