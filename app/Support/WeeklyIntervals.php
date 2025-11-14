<?php

namespace App\Support;

use Carbon\Carbon;

class WeeklyIntervals
{
    /**
     * Returns weekly intervals starting on each Friday of the given month.
     *
     * @return array<int,array{start:string,end:string}>
     */
    public static function forMonth(int $year, int $month): array
    {
        $date = Carbon::create($year, $month)->startOfMonth();
        $firstFriday = $date->isFriday() ? $date->copy() : $date->next(Carbon::FRIDAY);

        $intervals = [];

        for ($currentFriday = $firstFriday; $currentFriday->month === $month; $currentFriday->addWeek()) {
            $intervals[] = [
                'start' => $currentFriday->toDateString(),
                'end'   => $currentFriday->copy()->addDays(6)->toDateString(),
            ];
        }

        return $intervals;
    }
}
