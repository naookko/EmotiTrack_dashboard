<?php

// app/Services/MockAnalyticsApi.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MockAnalyticsApi
{
    public function overview(string $range = '7d'): array
    {
        return [
            'response_rate' => 0.78,
            'delta_response_rate' => 0.05,
            'dass_mean' => 22.3,
            'dass_delta' => -1.1,
            'high_risk_count' => 31,
            'high_risk_threshold' => 60,
        ];
    }

    public function dailyTrend(int $days = 30, array $filters = []): array
    {
        $out = [];
        $base = now()->subDays($days-1);
        for ($i=0; $i<$days; $i++) {
            $out[] = [
                'date'      => $base->copy()->addDays($i)->format('Y-m-d'),
                'stress'    => 12 + rand(0, 18),
                'anxiety'   => 10 + rand(0, 16),
                'depression'=>  8 + rand(0, 18),
            ];
        }
        return $out;
    }

    public function severityByWeek(int $weeks = 8, array $filters = []): array
    {
        $out = [];
        for ($i=$weeks-1; $i>=0; $i--) {
            $week = now()->startOfWeek()->subWeeks($i)->format('o-\WW');
            $normal = rand(30, 50);
            $leve = rand(15, 25);
            $moderado = rand(10, 20);
            $severo = rand(5, 15);
            $extremo = 100 - ($normal+$leve+$moderado+$severo);
            $out[] = compact('week','normal','leve','moderado','severo','extremo');
        }
        return $out;
    }

    public function clustersSummary(array $filters = []): array
    {
        return [
            ['id'=>0,'size'=>120,'centroid'=>['stress'=>18.2,'anxiety'=>15.1,'depression'=>13.4]],
            ['id'=>1,'size'=>45, 'centroid'=>['stress'=>27.9,'anxiety'=>24.2,'depression'=>22.0],'risk'=>'high'],
            ['id'=>2,'size'=>82, 'centroid'=>['stress'=>12.1,'anxiety'=>10.2,'depression'=>9.1]],
        ];
    }

    public function cohortTable(array $filters = []): array
    {
        return [
            ['cohort'=>'Ing. Info / 5 / BD', 'n'=>60, 'response_rate'=>0.73, 'dass_mean'=>24.1, 'severe_pct'=>0.21],
            ['cohort'=>'Ing. Info / 3 / SO', 'n'=>48, 'response_rate'=>0.81, 'dass_mean'=>20.4, 'severe_pct'=>0.12],
        ];
    }

    public function alerts(array $filters = []): array
    {
        return [
            ['type'=>'riesgo','cohort'=>'Ing. Info / 5','value'=>'Severo+Extremo 23% (+6 pp)','created_at'=>now()->toIso8601String()],
            ['type'=>'operación','cohort'=>'Webhook','value'=>'Errores 500: 12 en 1h','created_at'=>now()->toIso8601String()],
        ];
    }

    public function studentDetail(string $studentId): array
    {
        return [
            'student_id'=>$studentId,
            'weekly'=>[
                ['week'=>'2025-W36','stress'=>21,'anxiety'=>19,'depression'=>14,'cluster'=>2],
                ['week'=>'2025-W37','stress'=>18,'anxiety'=>15,'depression'=>13,'cluster'=>0],
            ],
            'flags'=>['no_response_streak'=>0,'spikes'=>1],
        ];
    }
}
