<?php

namespace App\Repositories;

class LineupRepository extends BaseRepository
{
    public function upsert(int $fixtureId, array $lineup): void
    {
        $data = [
            'fixture_id' => $fixtureId,
            'team_id' => $lineup['team']['id'],
            'formation' => $lineup['formation'] ?? null,
            'coach' => $lineup['coach']['name'] ?? null,
            'starting' => json_encode($lineup['startXI'] ?? []),
            'substitutes' => json_encode($lineup['substitutes'] ?? []),
        ];

        $this->insertOrUpdate('lineups', $data, ['fixture_id', 'team_id']);
    }
}
