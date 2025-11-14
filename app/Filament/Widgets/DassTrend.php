<?php

namespace App\Filament\Widgets;

use App\Http\Controllers\Mongo\ResponseController;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use MongoDB\BSON\UTCDateTime;

class DassTrend extends ChartWidget
{
    protected ?string $heading = 'Tendencia DASS-21';

    protected function getData(): array
    {
        [$year, $month] = $this->resolveFilterDate();
        $data = app(ResponseController::class)->getMonthlyResponsesData($year, $month);
        $intervals = $data['intervals'] ?? [];

        if (empty($intervals)) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $averages = $this->calculateWeeklyAverages($intervals);

        return [
            'datasets' => [
                [
                    'label' => 'Estrés',
                    'data' => $averages['stress'],
                    'borderColor' => 'rgba(59,130,246,1)',
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'tension' => 0.35,
                    'fill' => true,
                    'pointRadius' => 2,
                ],
                [
                    'label' => 'Ansiedad',
                    'data' => $averages['anxiety'],
                    'borderColor' => 'rgba(234,179,8,1)',
                    'backgroundColor' => 'rgba(234,179,8,0.2)',
                    'tension' => 0.35,
                    'fill' => true,
                    'pointRadius' => 2,
                ],
                [
                    'label' => 'Depresión',
                    'data' => $averages['depression'],
                    'borderColor' => 'rgba(239,68,68,1)',
                    'backgroundColor' => 'rgba(239,68,68,0.15)',
                    'tension' => 0.35,
                    'fill' => true,
                    'pointRadius' => 2,
                ],
            ],
            'labels' => $averages['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        $options = [];
        $cursor = now()->copy()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('Y-m');
            $options[$key] = $cursor->isoFormat('MMMM YYYY');
            $cursor->subMonth();
        }

        return $options;
    }

    protected function getDefaultFilter(): ?string
    {
        return now()->format('Y-m');
    }

    protected function getPollingInterval(): ?string
    {
        return null;
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'scales' => [
                'y' => ['beginAtZero' => true, 'title' => ['display' => true, 'text' => 'Puntaje promedio']],
                'x' => ['title' => ['display' => true, 'text' => 'Fecha']],
            ],
        ];
    }

    private function resolveFilterDate(): array
    {
        $filter = $this->filter ?? $this->getDefaultFilter();

        if ($filter && str_contains($filter, '-')) {
            [$year, $month] = explode('-', $filter, 2);
            return [(int) $year, (int) $month];
        }

        return [now()->year, now()->month];
    }

    private function calculateWeeklyAverages(array $intervals): array
    {
        $labels = [];
        $stress = [];
        $anxiety = [];
        $depression = [];

        foreach ($intervals as $interval) {
            $start = Carbon::parse($interval['start'])->startOfDay();
            $end = Carbon::parse($interval['end'])->endOfDay();

            $labels[] = sprintf(
                '%s - %s',
                $start->format('d M'),
                $end->format('d M')
            );

            $weekly = app(ResponseController::class)->getWeeklyAverages($start, $end);
            $stress[] = round((float) ($weekly['avg_stress_score'] ?? 0), 1);
            $anxiety[] = round((float) ($weekly['avg_anxiety_score'] ?? 0), 1);
            $depression[] = round((float) ($weekly['avg_depression_score'] ?? 0), 1);
        }

        return compact('labels', 'stress', 'anxiety', 'depression');
    }
}
