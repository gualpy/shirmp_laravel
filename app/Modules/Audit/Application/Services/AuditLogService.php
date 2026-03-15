<?php

namespace App\Modules\Audit\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Audit\Domain\Models\AuditLog;
use App\Multitenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

final class AuditLogService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly ?Request $request = null,
    ) {
    }

    /** @param array<string, mixed> $context */
    public function record(
        string $actionKey,
        string $entityType,
        ?int $entityId = null,
        array $context = [],
        ?Tenant $tenant = null,
        ?User $user = null,
    ): AuditLog {
        $resolvedTenant = $tenant ?? $this->tenantContext->currentTenant();
        $resolvedUser = $user ?? request()?->user();

        return AuditLog::query()->create([
            'tenant_id' => $resolvedTenant?->id,
            'user_id' => $resolvedUser?->id,
            'user_name' => $resolvedUser?->name,
            'action_key' => $actionKey,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'context_json' => $context === [] ? null : $context,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }

    /** @return Collection<int, AuditLog> */
    public function listForTenant(array $filters = []): Collection
    {
        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');

        return $this->applyFilters(AuditLog::query()->where('tenant_id', $tenant->id), $filters)
            ->orderByDesc('created_at')
            ->limit(250)
            ->get();
    }

    /** @return Collection<int, AuditLog> */
    public function listGlobalForSuperAdmin(array $filters = []): Collection
    {
        return $this->applyFilters(AuditLog::query()->with('tenant:id,name,slug'), $filters)
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();
    }

    private function applyFilters($query, array $filters)
    {
        return $query
            ->when(filled($filters['tenant_id'] ?? null), fn ($q) => $q->where('tenant_id', (int) $filters['tenant_id']))
            ->when(filled($filters['user'] ?? null), fn ($q) => $q->where('user_name', 'like', '%'.$filters['user'].'%'))
            ->when(filled($filters['action_key'] ?? null), fn ($q) => $q->where('action_key', $filters['action_key']))
            ->when(filled($filters['date_from'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']));
    }
}
