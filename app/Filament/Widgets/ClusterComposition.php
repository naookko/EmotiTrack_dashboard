<?php
//
//namespace App\Filament\Widgets;
//
//use Filament\Widgets\ChartWidget;
//use App\Services\AnalyticsApi;
//
//class ClusterComposition extends ChartWidget
//{
//    protected ?string $heading = 'Composición por clúster';
//
//    protected function getData(): array
//    {
////        $api = app(AnalyticsApi::class);
////        $clusters = $api->clustersSummary();
////
////        $labels = array_map(fn($c) => 'C'.$c['id'], $clusters);
////        $sizes  = array_map(fn($c) => $c['size'], $clusters);
////
////        return [
////            'datasets' => [
////                ['data' => $sizes]
////            ],
////            'labels' => $labels,
////        ];
//
//        $labels = [];
//        $stress = [];
//        $anxiety = [];
//        $depression = [];
//
//        $base = now()->subDays(29);
//        for ($i=0; $i<30; $i++) {
//            $labels[]     = $base->copy()->addDays($i)->format('Y-m-d');
//            $stress[]     = 12 + rand(0, 18); // 12–30
//            $anxiety[]    = 10 + rand(0, 16); // 10–26
//            $depression[] =  8 + rand(0, 18); //  8–26
//        }
//
//        return [
//            'datasets' => [
//                ['label' => 'Estrés',    'data' => $stress],
//                ['label' => 'Ansiedad',  'data' => $anxiety],
//                ['label' => 'Depresión', 'data' => $depression],
//            ],
//            'labels' => $labels,
//        ];
//    }
//
//    protected function getType(): string
//    {
//        return 'doughnut';
//    }
//
//    protected function getPollingInterval(): ?string
//    {
//        return null; // sin auto-refresco
//    }
//}


namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ClusterComposition extends ChartWidget
{
    protected ?string $heading = 'Composición por clúster (Estudiantes)';

    protected function getData(): array
    {
        $labels = ['Estrés', 'Ansiedad', 'Depresión'];

        $stressCount = 0;
        $anxietyCount = 0;
        $depressionCount = 0;

        // Base temporal: inicio de la semana hace 3 semanas
        $base = now()->startOfWeek()->subWeeks(3);

        // Simular 4 semanas
        for ($week = 0; $week < 4; $week++) {
            $weekStart = $base->copy()->addWeeks($week);
            $studentsPerWeek = rand(25, 40);

            for ($i = 0; $i < $studentsPerWeek; $i++) {
                // Simular puntuaciones
                $stress = 12 + rand(0, 18);
                $anxiety = 10 + rand(0, 16);
                $depression = 8 + rand(0, 18);

                // Determinar a qué clúster pertenece el estudiante
                if ($stress > $anxiety && $stress > $depression) {
                    $stressCount++;
                } elseif ($anxiety > $depression) {
                    $anxietyCount++;
                } else {
                    $depressionCount++;
                }
            }
        }

        // Totales del mes
        $data = [$stressCount, $anxietyCount, $depressionCount];

        return [
            'datasets' => [[
                'label' => 'Número de estudiantes',
                'data' => $data,
                'backgroundColor' => [
                    'rgba(255, 99, 132, 0.7)',   // Estrés
                    'rgba(54, 162, 235, 0.7)',   // Ansiedad
                    'rgba(255, 206, 86, 0.7)',   // Depresión
                ],
                'borderColor' => [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                ],
                'borderWidth' => 2,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getPollingInterval(): ?string
    {
        return null; // sin auto-refresco
    }
}
