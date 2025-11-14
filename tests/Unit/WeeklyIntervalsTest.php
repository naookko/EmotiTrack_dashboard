<?php

use App\Support\WeeklyIntervals;

it('starts intervals on the first Friday of the month', function () {
    $intervals = WeeklyIntervals::forMonth(2024, 3);

    expect($intervals)->toHaveCount(5);
    expect($intervals[0])->toBe([
        'start' => '2024-03-01',
        'end'   => '2024-03-07',
    ]);

    expect(WeeklyIntervals::firstStartDate($intervals))->toBe('2024-03-01');
});

it('keeps the final week even when it spills into the next month', function () {
    $intervals = WeeklyIntervals::forMonth(2025, 9);

    expect($intervals)->toBe([
        [
            'start' => '2025-09-05',
            'end'   => '2025-09-11',
        ],
        [
            'start' => '2025-09-12',
            'end'   => '2025-09-18',
        ],
        [
            'start' => '2025-09-19',
            'end'   => '2025-09-25',
        ],
        [
            'start' => '2025-09-26',
            'end'   => '2025-10-02',
        ],
    ]);

    expect(WeeklyIntervals::lastEndDate($intervals))->toBe('2025-10-02');
});
