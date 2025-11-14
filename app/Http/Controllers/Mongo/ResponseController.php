<?php

namespace App\Http\Controllers\Mongo;

use App\Http\Controllers\Controller;
use App\Models\Mongo\Response;
use App\Support\WeeklyIntervals;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use MongoDB\BSON\UTCDateTime;

class ResponseController extends Controller
{
    public function responsesByMonth(Request $request, int $month)
    {
        if ($month < 1 || $month > 12) {
            return response()->json(['message' => 'Invalid month provided.'], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $year = (int) $request->query('year', now()->year);

        return response()->json($this->getMonthlyResponsesData($year, $month));
    }

    public function getMonthlyResponsesData(int $year, int $month): array
    {
        $intervals = WeeklyIntervals::forMonth($year, $month);
        $startDate = WeeklyIntervals::firstStartDate($intervals);
        $endDate = WeeklyIntervals::lastEndDate($intervals);

        if (!$startDate || !$endDate) {
            return [
                'intervals' => [],
                'responses' => [],
            ];
        }

        $start = Carbon::parse($startDate)->startOfDay()->utc();
        $end = Carbon::parse($endDate)->endOfDay()->utc();

        $pipeline = [
            [
                '$match' => [
                    'created_at' => [
                        '$gte' => new UTCDateTime($start),
                        '$lte' => new UTCDateTime($end),
                    ],
                ],
            ],
            [
                '$project' => [
                    '_id'               => 0,
                    'wha_id'            => 1,
                    'stress_score'      => 1,
                    'depression_score'  => 1,
                    'total_score'       => 1,
                    'response_date'     => 1,
                    'created_at'        => 1,
                    'updated_at'        => 1,
                    'severity'          => [
                        '$switch' => [
                            'branches' => [
                                [
                                    'case' => ['$gte' => ['$total_score', 60]],
                                    'then' => 'Extremo',
                                ],
                                [
                                    'case' => ['$gte' => ['$total_score', 45]],
                                    'then' => 'Severo',
                                ],
                                [
                                    'case' => ['$gte' => ['$total_score', 30]],
                                    'then' => 'Moderado',
                                ],
                                [
                                    'case' => ['$gte' => ['$total_score', 20]],
                                    'then' => 'Leve',
                                ],
                            ],
                            'default' => 'Normal',
                        ],
                    ],
                ],
            ],
            [
                '$sort' => [
                    'created_at' => 1,
                ],
            ],
        ];

        $responses = Response::raw(fn ($collection) => $collection->aggregate($pipeline)->toArray());

        return [
            'intervals' => $intervals,
            'responses' => $responses,
        ];
    }

    public function monthlySummary(Request $request)
    {
        $validated = $request->validate([
            'year'        => ['required', 'integer', 'min:1970'],
            'start_month' => ['required', 'integer', 'between:1,12'],
            'end_month'   => ['required', 'integer', 'between:1,12', 'gte:start_month'],
        ]);

        $start = Carbon::create($validated['year'], $validated['start_month'])->startOfMonth()->utc();
        $end = Carbon::create($validated['year'], $validated['end_month'])->endOfMonth()->utc();

        $pipeline = [
            [
                '$match' => [
                    'response_date' => [
                        '$gte' => new UTCDateTime($start),
                        '$lte' => new UTCDateTime($end),
                    ],
                ],
            ],
            [
                '$group' => [
                    '_id' => [
                        'year' => ['$year' => '$response_date'],
                        'month' => ['$month' => '$response_date'],
                    ],
                    'total_responses' => ['$sum' => 1],
                    'unique_students' => ['$addToSet' => '$wha_id'],
                ],
            ],
            [
                '$project' => [
                    '_id' => 0,
                    'year' => '$_id.year',
                    'month' => '$_id.month',
                    'total_responses' => 1,
                    'total_unique_students' => ['$size' => '$unique_students'],
                ],
            ],
            [
                '$sort' => [
                    'year' => 1,
                    'month' => 1,
                ],
            ],
        ];

        $summary = Response::raw(fn ($collection) => $collection->aggregate($pipeline)->toArray());

        return response()->json($summary);
    }
}
