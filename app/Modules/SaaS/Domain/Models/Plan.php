<?php

namespace App\Modules\SaaS\Domain\Models;

use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'billing_type',
        'price_usd',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'billing_type' => PlanBillingType::class,
            'price_usd' => 'decimal:2',
            'is_active' => 'bool',
        ];
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PlanLimit::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function periodEndFrom(CarbonImmutable $start): CarbonImmutable
    {
        return match ($this->billing_type) {
            PlanBillingType::YEARLY => $start->addYear(),
            PlanBillingType::LIFETIME => $start->addYears(100),
            default => $start->addMonthNoOverflow(),
        };
    }
}

