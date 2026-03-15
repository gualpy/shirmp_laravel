<?php

namespace App\Modules\Billing\Domain\Models;

use App\Models\Tenant;
use App\Modules\Billing\Domain\Enums\BillingInvoiceStatus;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingInvoice extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'billing_period_start',
        'billing_period_end',
        'amount_usd',
        'currency',
        'status',
        'issued_at',
        'due_at',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BillingInvoiceStatus::class,
            'billing_period_start' => 'date',
            'billing_period_end' => 'date',
            'amount_usd' => 'decimal:2',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'subscription_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class, 'invoice_id');
    }
}
