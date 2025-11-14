<?php

use App\Http\Controllers\Mongo\Dass21WeekResultController;

class FakeDass21WeekResultController extends Dass21WeekResultController
{
    public array $aggregateResult = [];
    public array $capturedPipeline = [];

    protected function aggregateClusterData(array $pipeline): array
    {
        $this->capturedPipeline = $pipeline;
        return $this->aggregateResult;
    }
}

beforeEach(function () {
    $this->controller = new FakeDass21WeekResultController();
});

it('returns null when week is out of range', function () {
    expect($this->controller->clusterByWeekData(2025, 9, 0))->toBeNull();
    expect($this->controller->clusterByWeekData(2025, 9, 6))->toBeNull();
});

it('returns empty default data when week interval is missing', function () {
    $result = $this->controller->clusterByWeekData(2025, 2, 5); // February 2025 only has 4 Fridays

    expect($result)->toMatchArray([
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
    ]);
});

it('aggregates cluster counts for the requested interval', function () {
    $this->controller->aggregateResult = [
        ['_id' => 'cluster_1', 'total' => 4],
        ['_id' => 'cluster_2', 'total' => 7],
        ['_id' => 'cluster_3', 'total' => 2],
    ];

    $result = $this->controller->clusterByWeekData(2025, 9, 1);

    expect($result['data'])->toMatchArray([
        'cluster_1' => 4,
        'cluster_2' => 7,
        'cluster_3' => 2,
    ]);

    expect($result['interval'])->toMatchArray([
        'start' => '2025-09-05',
        'end' => '2025-09-11',
    ]);

    // Ensure pipeline uses the computed week range
    $match = $this->controller->capturedPipeline[0]['$match']['week_start_date'];
    expect($match)->toHaveKeys(['$gte', '$lte']);
});
