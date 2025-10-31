<?php

namespace App\Repositories;

class StatisticRepository extends BaseRepository
{
    public function upsert(int $fixtureId, array $stat): void
    {
        $data = [
            'fixture_id' => $fixtureId,
            'team_id' => $stat['team']['id'],
            'type' => $stat['type'],
            'value' => is_array($stat['value']) ? json_encode($stat['value']) : ($stat['value'] ?? null),
        ];

        $this->insertOrUpdate('statistics', $data, ['fixture_id', 'team_id', 'type']);
    }
}
