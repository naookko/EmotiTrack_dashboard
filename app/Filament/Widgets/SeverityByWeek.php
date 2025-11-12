<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Services\AnalyticsApi;

class SeverityByWeek extends ChartWidget
{
    protected ?string $heading = 'Severidad por semana';

    protected function getData(): array
    {
        $api = app(AnalyticsApi::class);
        $rows = $api->severityByWeek(8);

        $labels = array_column($rows, 'week');
        $norm   = array_column($rows, 'normal');
        $mild   = array_column($rows, 'leve');
        $mod    = array_column($rows, 'moderado');
        $sev    = array_column($rows, 'severo');
        $ext    = array_column($rows, 'extremo');

        return [
            'datasets' => [
                ['label'=>'Normal',   'data'=>$norm, 'stack'=>'s'],
                ['label'=>'Leve',     'data'=>$mild, 'stack'=>'s'],
                ['label'=>'Moderado', 'data'=>$mod,  'stack'=>'s'],
                ['label'=>'Severo',   'data'=>$sev,  'stack'=>'s'],
                ['label'=>'Extremo',  'data'=>$ext,  'stack'=>'s'],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
