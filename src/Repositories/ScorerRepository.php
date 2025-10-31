<?php

namespace App\Repositories;

class ScorerRepository extends BaseRepository
{
    public function upsert(array $record): void
    {
        $statistics = $record['statistics'][0] ?? [];
        $goals = $statistics['goals'] ?? [];

        $data = [
            'league_id' => $record['league_id'],
            'season' => $record['season'],
            'player_id' => $record['player']['id'],
            'team_id' => $statistics['team']['id'] ?? null,
            'rank' => $record['rank'] ?? null,
            'games' => $statistics['games']['appearences'] ?? null,
            'goals' => $goals['total'] ?? null,
            'assists' => $goals['assists'] ?? null,
            'penalties' => $goals['penalty'] ?? null,
        ];

        $this->insertOrUpdate('scorers', $data, ['league_id', 'season', 'player_id']);
    }
}
