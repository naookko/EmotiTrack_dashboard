<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Mongo\ScoreExport;
use MongoDB\BSON\UTCDateTime;

class DassTrend extends ChartWidget
{
    protected ?string $heading = 'Tendencia DASS-21 (30 días)';

    protected function getData(): array
    {
//        $from = now()->startOfDay()->subDays(29);
//
//        $rows = ScoreExport::raw(function ($c) use ($from) {
//            return $c->aggregate([
//                ['$addFields' => [
//                    'created_at' => ['$toDate' => '$_id'],
//                    'stress'     => ['$ifNull' => ['$stress_score', 0]],
//                    'anxiety'    => ['$ifNull' => ['$anxiety_score', 0]],
//                    'depression' => ['$ifNull' => ['$depression_score', 0]],
//                ]],
//                ['$match' => [
//                    'created_at' => ['$gte' => new UTCDateTime($from->getTimestamp() * 1000)],
//                ]],
//                ['$group' => [
//                    '_id'        => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']],
//                    'stress'     => ['$avg' => '$stress'],
//                    'anxiety'    => ['$avg' => '$anxiety'],
//                    'depression' => ['$avg' => '$depression'],
//                ]],
//                ['$sort' => ['_id' => 1]],
//            ]);
//        })->toArray();
//
//        // Normaliza a arrays y lee con índices
//        $labels = [];
//        $stress = [];
//        $anx    = [];
//        $dep    = [];
//
//        foreach ($rows as $r) {
//            // Garantiza array
//            $row = (array) $r;
//
//            $labels[] = $row['_id'] ?? '';
//            $stress[] = isset($row['stress'])     ? round((float) $row['stress'], 1)     : 0.0;
//            $anx[]    = isset($row['anxiety'])    ? round((float) $row['anxiety'], 1)    : 0.0;
//            $dep[]    = isset($row['depression']) ? round((float) $row['depression'], 1) : 0.0;
//        }

        $base = now()->startOfWeek()->subWeeks(3); // hace 3 semanas desde el inicio de esta
        $labels = [];
        $stress = [];
        $anxiety = [];
        $depression = [];

        for ($week = 0; $week < 4; $week++) { // 4 semanas = 1 mes aprox
            $weekStart = $base->copy()->addWeeks($week);

            // Simular muchos estudiantes por semana (ej. 30)
            $studentsPerWeek = rand(25, 40);

            $weekStress = 0;
            $weekAnxiety = 0;
            $weekDepression = 0;

            for ($i = 0; $i < $studentsPerWeek; $i++) {
                $weekStress += 12 + rand(0, 18);
                $weekAnxiety += 10 + rand(0, 16);
                $weekDepression += 8 + rand(0, 18);
            }

            // Calcular promedios
            $avgStress = round($weekStress / $studentsPerWeek, 1);
            $avgAnxiety = round($weekAnxiety / $studentsPerWeek, 1);
            $avgDepression = round($weekDepression / $studentsPerWeek, 1);

            // Registrar un solo punto por semana
            $labels[] = $weekStart->format('Y-m-d');
            $stress[] = $avgStress;
            $anxiety[] = $avgAnxiety;
            $depression[] = $avgDepression;
        }




        return [
            'datasets' => [
                [
                    'label' => 'Estrés',
                    'data'  => $stress,
                    'borderColor'     => 'rgba(59,130,246,1)',
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'tension' => 0.35, 'fill' => true, 'pointRadius' => 2,
                ],
                [
                    'label' => 'Ansiedad',
                    'data'  => $anxiety,
                    'borderColor'     => 'rgba(234,179,8,1)',
                    'backgroundColor' => 'rgba(234,179,8,0.2)',
                    'tension' => 0.35, 'fill' => true, 'pointRadius' => 2,
                ],
                [
                    'label' => 'Depresión',
                    'data'  => $depression,
                    'borderColor'     => 'rgba(239,68,68,1)',
                    'backgroundColor' => 'rgba(239,68,68,0.15)',
                    'tension' => 0.35, 'fill' => true, 'pointRadius' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getPollingInterval(): ?string
    {
        return null; // sin auto-refresco
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend'  => ['position' => 'bottom'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'scales' => [
                'y' => ['beginAtZero' => true, 'title' => ['display' => true, 'text' => 'Puntaje promedio']],
                'x' => ['title' => ['display' => true, 'text' => 'Fecha']],
            ],
        ];
    }
}
