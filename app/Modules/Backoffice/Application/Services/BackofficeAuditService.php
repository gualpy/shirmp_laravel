<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Models\Tenant;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Audit\Domain\Models\AuditLog;

final class BackofficeAuditService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /** @return array<string, mixed> */
    public function tenantView(array $filters = []): array
    {
        $rows = $this->auditLogService->listForTenant($filters)
            ->map(fn (AuditLog $log): array => $this->mapRow($log, false))
            ->values()
            ->all();

        return [
            'rows' => $rows,
            'filters' => $filters,
            'action_keys' => collect($rows)->pluck('action_key')->unique()->sort()->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function globalView(array $filters = []): array
    {
        $rows = $this->auditLogService->listGlobalForSuperAdmin($filters)
            ->map(fn (AuditLog $log): array => $this->mapRow($log, true))
            ->values()
            ->all();

        return [
            'rows' => $rows,
            'filters' => $filters,
            'action_keys' => collect($rows)->pluck('action_key')->unique()->sort()->values()->all(),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name', 'slug'])->map(fn (Tenant $tenant): array => [
                'id' => $tenant->id,
                'label' => $tenant->name.' ('.$tenant->slug.')',
            ])->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function mapRow(AuditLog $log, bool $includeTenant): array
    {
        $context = is_array($log->context_json) ? $log->context_json : [];
        $summary = collect($context)
            ->map(fn ($value, $key): string => $key.'='. (is_scalar($value) || $value === null ? (string) $value : json_encode($value)))
            ->take(3)
            ->implode(' · ');

        return [
            'id' => $log->id,
            'tenant' => $includeTenant ? ($log->tenant?->name ?? 'Global') : null,
            'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
            'user_name' => $log->user_name ?? 'Sistema',
            'action_key' => $log->action_key,
            'entity_type' => $log->entity_type,
            'entity_id' => $log->entity_id,
            'context_summary' => $summary !== '' ? $summary : 'Sin contexto adicional',
        ];
    }
}
