<?php

namespace App\Models;

use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Configuration\Domain\Models\TenantSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'name',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'bool',
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(TenantNote::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(TenantSetting::class);
    }

    public function feedingTables(): HasMany
    {
        return $this->hasMany(FeedingGrowthTable::class);
    }
}
