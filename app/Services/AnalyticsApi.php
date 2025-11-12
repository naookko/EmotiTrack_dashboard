<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AnalyticsApi
{
    public function __construct(
        private string $base = '',
        private int $ttl = 600,
    ) {
        $this->base = config('services.bot_api.base', env('BOT_API'));
        $this->ttl  = (int) env('CACHE_TTL', 600);
    }

    private function get(string $path, array $query = []): array
    {
        $url = rtrim($this->base, '/').'/'.ltrim($path, '/');
        $key = 'api:'.$path.':'.md5(json_encode($query));

        return Cache::remember($key, $this->ttl, function () use ($url, $query) {
            $res = Http::timeout((int)env('BOT_API_TIMEOUT', 6))
                ->acceptJson()->get($url, $query);

            $res->throw();
            return $res->json();
        });
    }

    // Endpoints típicos
    public function overview(string $range = '7d'): array
    {
        return $this->get('/analytics/overview', ['range' => $range]);
    }

    public function dailyTrend(int $days = 30, array $filters = []): array
    {
        return $this->get('/analytics/daily-trend', ['days'=>$days] + $filters);
    }

    public function severityByWeek(int $weeks = 8, array $filters = []): array
    {
        return $this->get('/analytics/severity-week', ['weeks'=>$weeks] + $filters);
    }

    public function clustersSummary(array $filters = []): array
    {
        return $this->get('/analytics/clusters', $filters);
    }

    public function cohortTable(array $filters = []): array
    {
        return $this->get('/analytics/cohorts', $filters);
    }

    public function alerts(array $filters = []): array
    {
        return $this->get('/analytics/alerts', $filters);
    }

    public function studentDetail(string $studentId): array
    {
        return $this->get("/students/{$studentId}/summary");
    }
}
