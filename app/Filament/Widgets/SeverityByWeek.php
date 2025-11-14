<?php

namespace App\Filament\Widgets;

use App\Http\Controllers\Mongo\ResponseController;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use MongoDB\BSON\UTCDateTime;

class SeverityByWeek extends ChartWidget
{
    protected ?string $heading = 'Severidad por mes';

    protected function getData(): array
    {
        $request = request();
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $data = $this->fetchResponsesWithSeverity($year, $month);
        $intervals = $data['intervals'] ?? [];
        $responses = $data['responses'] ?? [];

        if (empty($intervals)) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        return $this->buildDatasets($intervals, $responses);
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function fetchResponsesWithSeverity(int $year, int $month): array
    {
        return app(ResponseController::class)->getMonthlyResponsesData($year, $month);
    }

    private function buildDatasets(array $intervals, array $responses): array
    {
        $labels = [];
        $bounds = [];

        foreach ($intervals as $interval) {
            $start = Carbon::parse($interval['start'])->startOfDay();
            $end = Carbon::parse($interval['end'])->endOfDay();
            $bounds[] = compact('start', 'end');

            $labels[] = sprintf(
                '%s - %s',
                $start->format('d M'),
                $end->format('d M')
            );
        }

        $series = [
            'normal' => array_fill(0, count($intervals), 0),
            'leve' => array_fill(0, count($intervals), 0),
            'moderado' => array_fill(0, count($intervals), 0),
            'severo' => array_fill(0, count($intervals), 0),
            'extremo' => array_fill(0, count($intervals), 0),
        ];

        $map = [
            'Normal' => 'normal',
            'Leve' => 'leve',
            'Moderado' => 'moderado',
            'Severo' => 'severo',
            'Extremo' => 'extremo',
        ];

        foreach ($responses as $response) {
            $createdAt = $this->asUtcCarbon($response['created_at'] ?? null);
            if (!$createdAt) {
                continue;
            }

            $severityKey = $map[$response['severity'] ?? 'Normal'] ?? 'normal';

            foreach ($bounds as $index => $range) {
                if ($createdAt->between($range['start'], $range['end'], true)) {
                    $series[$severityKey][$index]++;
                    break;
                }
            }
        }

        return [
            'datasets' => [
                ['label' => 'Normal',   'data' => $series['normal'],   'stack' => 's'],
                ['label' => 'Leve',     'data' => $series['leve'],     'stack' => 's'],
                ['label' => 'Moderado', 'data' => $series['moderado'], 'stack' => 's'],
                ['label' => 'Severo',   'data' => $series['severo'],   'stack' => 's'],
                ['label' => 'Extremo',  'data' => $series['extremo'],  'stack' => 's'],
            ],
            'labels' => $labels,
        ];
    }

    private function asUtcCarbon($value): ?Carbon
    {
        if ($value instanceof UTCDateTime) {
            return Carbon::instance($value->toDateTime())->utc();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->utc();
        }

        if (is_string($value)) {
            return Carbon::parse($value)->utc();
        }

        return null;
    }
}
