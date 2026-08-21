<?php

namespace App\Modules\SaaS\Application\Mail;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\SaaS\Domain\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class PlanChangedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    private const LIMIT_LABELS = [
        'max_farms' => 'granjas',
        'max_ponds' => 'piscinas',
        'max_cycles_active' => 'ciclos activos',
        'max_users' => 'usuarios',
        'max_storage_mb' => 'MB de almacenamiento',
    ];

    private const FEATURE_LABELS = [
        'dashboard' => 'Panel de control',
        'alerts' => 'Alertas automáticas',
        'cost_engine' => 'Motor de costos',
        'water_quality' => 'Calidad de agua',
        'advanced_reports' => 'Reportes avanzados',
        'api_access' => 'Acceso a API',
        'export_excel' => 'Exportar a Excel',
        'export_pdf' => 'Exportar a PDF',
    ];

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $owner,
        public readonly Plan $previousPlan,
        public readonly Plan $newPlan,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('mail.plan_changed_subject', ['app' => config('app.name')]))
            ->view('emails.saas.plan-changed')
            ->with([
                'tenant' => $this->tenant,
                'owner' => $this->owner,
                'previousPlan' => $this->previousPlan,
                'newPlan' => $this->newPlan,
                'loginUrl' => route('login', ['tenant' => $this->tenant->slug]),
                'limits' => $this->formatLimits(),
                'features' => $this->formatFeatures(),
            ]);
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private function formatLimits(): array
    {
        return $this->newPlan->limits()
            ->get()
            ->filter(fn ($limit) => array_key_exists($limit->key, self::LIMIT_LABELS))
            ->map(fn ($limit) => [
                'label' => self::LIMIT_LABELS[$limit->key],
                'value' => $limit->value === null ? 'Ilimitado' : 'Hasta '.$limit->value,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, is_new: bool}>
     */
    private function formatFeatures(): array
    {
        $previouslyEnabled = $this->previousPlan->features()
            ->where('is_enabled', true)
            ->pluck('feature_key')
            ->all();

        return $this->newPlan->features()
            ->where('is_enabled', true)
            ->get()
            ->filter(fn ($feature) => array_key_exists($feature->feature_key, self::FEATURE_LABELS))
            ->map(fn ($feature) => [
                'label' => self::FEATURE_LABELS[$feature->feature_key],
                'is_new' => ! in_array($feature->feature_key, $previouslyEnabled, true),
            ])
            ->values()
            ->all();
    }
}
