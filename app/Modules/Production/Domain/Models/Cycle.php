<?php

namespace App\Modules\Production\Domain\Models;

use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use App\Models\Tenant;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cycle extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'pond_id',
        'status',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CycleStatus::class,
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pond(): BelongsTo
    {
        return $this->belongsTo(Pond::class);
    }

    public function stocking(): HasOne
    {
        return $this->hasOne(Stocking::class);
    }

    public function samplings(): HasMany
    {
        return $this->hasMany(Sampling::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    public function feedEntries(): HasMany
    {
        return $this->hasMany(FeedEntry::class);
    }

    public function operationalCosts(): HasMany
    {
        return $this->hasMany(OperationalCostEntry::class);
    }

    public function waterQualityEntries(): HasMany
    {
        return $this->hasMany(WaterQualityEntry::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(AlertEvent::class);
    }

    public function survivalEstimates(): HasMany
    {
        return $this->hasMany(SurvivalEstimate::class);
    }

    public function dailyMortalities(): HasMany
    {
        return $this->hasMany(DailyMortality::class);
    }
}
