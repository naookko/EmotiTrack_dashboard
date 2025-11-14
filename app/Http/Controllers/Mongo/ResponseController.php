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

        $intervals = WeeklyIntervals::forMonth($year, $month);
        $startDate = WeeklyIntervals::firstStartDate($intervals);
        $endDate = WeeklyIntervals::lastEndDate($intervals);

        if (!$startDate || !$endDate) {
            return response()->json([]);
        }

        $responses = Response::query()
            ->select([
                'wha_id',
                'stress_score',
                'depression_score',
                'total_score',
                'response_date',
                'created_at',
                'updated_at',
            ])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'intervals' => $intervals,
            'responses' => $responses,
        ]);
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
