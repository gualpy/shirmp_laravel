<?php

namespace App\Modules\Feeding\Domain\Models;

use App\Models\Tenant;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedType extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'brand',
        'protein_pct',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'protein_pct' => 'decimal:2',
            'is_active' => 'bool',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FeedEntry::class);
    }
}
