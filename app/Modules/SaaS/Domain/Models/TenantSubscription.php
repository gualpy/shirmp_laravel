<?php

namespace App\Modules\SaaS\Domain\Models;

use App\Models\Tenant;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Enums\VerificationSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'renewal_reminder_sent_at',
        'expired_notification_sent_at',
        'license_key',
        'last_verified_at',
        'offline_grace_days',
        'offline_mode_enabled',
        'verification_source',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'renewal_reminder_sent_at' => 'datetime',
            'expired_notification_sent_at' => 'datetime',
            'last_verified_at' => 'datetime',
            'offline_grace_days' => 'int',
            'offline_mode_enabled' => 'bool',
            'verification_source' => VerificationSource::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class, 'subscription_id');
    }
}
