<?php

namespace App\Modules\Billing\Domain\Models;

use App\Models\Tenant;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\Billing\Domain\Enums\BillingPaymentStatus;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPayment extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'amount_usd',
        'currency',
        'status',
        'provider',
        'provider_reference',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BillingPaymentStatus::class,
            'provider' => BillingPaymentProvider::class,
            'amount_usd' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }
}
