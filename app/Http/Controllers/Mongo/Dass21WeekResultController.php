<?php

namespace App\Http\Controllers\Mongo;

use App\Http\Controllers\Controller;
use App\Models\Mongo\Dass21WeekResult;
use App\Support\WeeklyIntervals;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use MongoDB\BSON\UTCDateTime;

class Dass21WeekResultController extends Controller
{
    public function clusterByWeek(Request $request, int $week)
    {
        $year = (int) $request->query('year', now()->subMonth()->year);
        $month = (int) $request->query('month', now()->subMonth()->month);

        $data = $this->clusterByWeekData($year, $month, $week);

        if ($data === null) {
            return response()->json(['message' => 'Invalid week provided.'], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($data);
    }

    public function clusterByWeekData(int $year, int $month, int $week): ?array
    {
        if ($week < 1 || $week > 5) {
            return null;
        }

        $intervals = WeeklyIntervals::forMonth($year, $month);

        if (empty($intervals) || !isset($intervals[$week - 1])) {
            return [
                'labels' => [
                    'cluster_1' => 'Cluster 1',
                    'cluster_2' => 'Cluster 2',
                    'cluster_3' => 'Cluster 3',
                ],
                'data' => [
                    'cluster_1' => 0,
                    'cluster_2' => 0,
                    'cluster_3' => 0,
                ],
            ];
        }

        $interval = $intervals[$week - 1];
        $start = Carbon::parse($interval['start'])->startOfDay()->utc();
        $end = Carbon::parse($interval['end'])->endOfDay()->utc();

        $pipeline = [
            [
                '$match' => [
                    'week_start_date' => [
                        '$gte' => new UTCDateTime($start),
                        '$lte' => new UTCDateTime($end),
                    ],
                ],
            ],
            [
                '$group' => [
                    '_id' => '$cluster',
                    'total' => ['$sum' => 1],
                ],
            ],
        ];

        $results = $this->aggregateClusterData($pipeline);

        $data = [
            'cluster_1' => 0,
            'cluster_2' => 0,
            'cluster_3' => 0,
        ];

        foreach ($results as $result) {
            $id = is_array($result) ? $result['_id'] : $result->offsetGet('_id');
            $count = is_array($result) ? $result['total'] : $result->offsetGet('total');

            if (isset($data[$id])) {
                $data[$id] = (int) $count;
            }
        }

        return [
            'labels' => [
                'cluster_1' => 'Cluster Riesgo Medio',
                'cluster_2' => 'Cluster Riesgo Alto',
                'cluster_3' => 'Cluster Riesgo Crítico',
            ],
            'data' => $data,
            'interval' => $interval,
        ];
    }

    protected function aggregateClusterData(array $pipeline): array
    {
        return Dass21WeekResult::raw(fn ($collection) => $collection->aggregate($pipeline)->toArray());
    }
}
