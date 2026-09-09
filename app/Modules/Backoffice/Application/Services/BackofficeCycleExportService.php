<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Backoffice\Presentation\Exports\CycleArrayExport;
use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeCycleExportService
{
    public function __construct(private readonly CostingService $costingService)
    {
    }

    public function downloadSamplingsXlsx(Cycle $cycle): BinaryFileResponse
    {
        $export = $this->samplingsExport($cycle);

        return $this->xlsxResponse($cycle, 'samplings', 'Samplings', $export['headers'], $export['rows']);
    }

    public function downloadFeedXlsx(Cycle $cycle): BinaryFileResponse
    {
        $export = $this->feedExport($cycle);

        return $this->xlsxResponse($cycle, 'feed', 'Feed', $export['headers'], $export['rows']);
    }

    public function downloadMortalitiesXlsx(Cycle $cycle): BinaryFileResponse
    {
        $export = $this->mortalitiesExport($cycle);

        return $this->xlsxResponse($cycle, 'mortalities', 'Mortalities', $export['headers'], $export['rows']);
    }

    public function downloadWaterXlsx(Cycle $cycle): BinaryFileResponse
    {
        $export = $this->waterExport($cycle);

        return $this->xlsxResponse($cycle, 'water', 'Water Quality', $export['headers'], $export['rows']);
    }

    public function downloadCostsXlsx(Cycle $cycle): BinaryFileResponse
    {
        $rows = $cycle->feedEntries()
            ->with('feedType:id,name,cost_per_kg')
            ->orderBy('fed_at')
            ->get()
            ->map(fn ($entry): array => [
                (string) ($entry->feedType?->name ?? 'feed'),
                $entry->feedType?->cost_per_kg !== null ? round((float) $entry->amount_kg * (float) $entry->feedType->cost_per_kg, 2) : 0.0,
                $entry->fed_at?->format('Y-m-d'),
                $entry->notes,
                'feed_cost',
            ])
            ->all();

        $operationalRows = $cycle->operationalCosts()
            ->orderBy('occurred_at')
            ->get()
            ->map(fn ($entry): array => [
                is_string($entry->cost_type) ? $entry->cost_type : $entry->cost_type->value,
                (float) $entry->amount,
                $entry->occurred_at?->format('Y-m-d'),
                $entry->notes,
                'operational_cost',
            ])
            ->all();

        return $this->xlsxResponse(
            $cycle,
            'costs',
            'Cycle Costs',
            ['type', 'amount', 'occurred_at', 'notes', 'source'],
            [...$rows, ...$operationalRows]
        );
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function xlsxResponse(Cycle $cycle, string $suffix, string $title, array $headers, array $rows): BinaryFileResponse
    {
        return Excel::download(
            new CycleArrayExport($title, $headers, $rows),
            $this->filename($cycle, $suffix, 'xlsx')
        );
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function samplingsExport(Cycle $cycle): array
    {
        return [
            'headers' => ['sampled_at', 'pp_grams', 'notes'],
            'rows' => $cycle->samplings()
                ->orderBy('sampled_at')
                ->get()
                ->map(fn ($sampling): array => [
                    $sampling->sampled_at?->format('Y-m-d'),
                    $sampling->pp_grams,
                    $sampling->notes,
                ])
                ->all(),
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function feedExport(Cycle $cycle): array
    {
        return [
            'headers' => ['fed_at', 'feed_type', 'amount_kg', 'notes'],
            'rows' => $cycle->feedEntries()
                ->with('feedType:id,name')
                ->orderBy('fed_at')
                ->get()
                ->map(fn ($entry): array => [
                    $entry->fed_at?->format('Y-m-d H:i'),
                    (string) ($entry->feedType?->name ?? 'N/A'),
                    $entry->amount_kg,
                    $entry->notes,
                ])
                ->all(),
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function mortalitiesExport(Cycle $cycle): array
    {
        return [
            'headers' => ['recorded_at', 'pond', 'mortality_count', 'notes'],
            'rows' => $cycle->dailyMortalities()
                ->with('pond:id,code')
                ->orderBy('recorded_at')
                ->get()
                ->map(fn ($entry): array => [
                    $entry->recorded_at?->format('Y-m-d'),
                    (string) ($entry->pond?->code ?? 'N/A'),
                    $entry->mortality_count,
                    $entry->notes,
                ])
                ->all(),
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function waterExport(Cycle $cycle): array
    {
        return [
            'headers' => ['measured_at', 'do', 'ph', 'temperature', 'salinity', 'alkalinity', 'ammonia', 'nitrite', 'notes'],
            'rows' => $cycle->waterQualityEntries()
                ->orderBy('measured_at')
                ->get()
                ->map(fn ($entry): array => [
                    $entry->measured_at?->format('Y-m-d H:i'),
                    $entry->dissolved_oxygen_mg_l,
                    $entry->ph,
                    $entry->temp_c,
                    $entry->salinity_ppt,
                    $entry->alkalinity_mg_l,
                    $entry->ammonia_mg_l,
                    $entry->nitrite_mg_l,
                    $entry->notes,
                ])
                ->all(),
        ];
    }

    private function filename(Cycle $cycle, string $suffix, string $extension): string
    {
        $cycle->loadMissing('pond');

        return sprintf(
            '%s.%s',
            Str::slug('cycle-'.$cycle->id.'-'.$cycle->pond?->code.'-'.$suffix),
            $extension
        );
    }
}
