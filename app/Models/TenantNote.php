<?php

namespace App\Models;

use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantNote extends Model
{
    use HasFactory;
    use HasTenant;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'note',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
