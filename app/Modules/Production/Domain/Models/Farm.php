<?php

namespace App\Modules\Production\Domain\Models;

use App\Modules\Configuration\Domain\Models\FarmSetting;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Models\Tenant;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Farm extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'company_display_name',
        'logo_path',
        'location',
        'notes',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function ponds(): HasMany
    {
        return $this->hasMany(Pond::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(FarmSetting::class);
    }

    public function feedingTables(): HasMany
    {
        return $this->hasMany(FeedingGrowthTable::class);
    }
}
