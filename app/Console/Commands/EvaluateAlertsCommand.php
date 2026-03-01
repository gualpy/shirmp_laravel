<?php

namespace App\Console\Commands;

use App\Modules\Alerts\Application\Services\AlertEngineService;
use App\Multitenancy\TenantScopeBypass;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class EvaluateAlertsCommand extends Command
{
    protected $signature = 'alerts:evaluate {--date=}';

    protected $description = 'Evaluate alert rules for active cycles and generate alert events';

    public function handle(AlertEngineService $engine, TenantScopeBypass $scopeBypass): int
    {
        $date = $this->option('date') ? Carbon::parse((string) $this->option('date')) : now();

        $countCycles = 0;
        $countAlerts = 0;

        $scopeBypass->run(function () use ($engine, $date, &$countCycles, &$countAlerts): void {
            Cycle::withoutGlobalScopes()
                ->where('status', CycleStatus::ACTIVE->value)
                ->chunkById(100, function ($cycles) use ($engine, $date, &$countCycles, &$countAlerts): void {
                    foreach ($cycles as $cycle) {
                        $countCycles++;
                        $countAlerts += count($engine->evaluateCycle($cycle, $date));
                    }
                });
        });

        $this->info("Evaluated {$countCycles} cycles, created {$countAlerts} alerts.");

        return self::SUCCESS;
    }
}
