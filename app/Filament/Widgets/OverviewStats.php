<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsApi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $api = app(AnalyticsApi::class);
        $data = $api->overview('7d');

        return [
            Stat::make('Participación (7d)', number_format($data['response_rate']*100,1).'%')
                ->description(($data['delta_response_rate']>=0?'+':'').number_format($data['delta_response_rate']*100,1).' pp')
                ->descriptionIcon($data['delta_response_rate']>=0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'),

            Stat::make('DASS-21 Prom.', number_format($data['dass_mean'],1))
                ->description('Δ 7d '.($data['dass_delta']>=0?'+':'').number_format($data['dass_delta'],1)),

            Stat::make('Alto riesgo', (string)$data['high_risk_count'])
                ->description('Umbral '.$data['high_risk_threshold']),
        ];
    }
}
