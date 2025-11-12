<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClusterComposition;
use App\Filament\Widgets\DassTrend;
use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\SeverityByWeek;
use Filament\Pages\Page;

class ClusteringDashboard extends Page
{

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-squares-2x2';
    protected string $view = 'filament.pages.clustering-dashboard';
    protected static string|null|\UnitEnum $navigationGroup = 'Análisis';

    protected function getHeaderWidgets(): array
    {
        return [OverviewStats::class];
    }

    protected function getFooterWidgets(): array
    {
        return [DassTrend::class, SeverityByWeek::class, ClusterComposition::class];
    }
}
