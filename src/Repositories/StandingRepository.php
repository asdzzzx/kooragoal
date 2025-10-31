<?php

namespace App\Repositories;

class StandingRepository extends BaseRepository
{
    public function upsert(array $record): void
    {
        $data = [
            'league_id' => $record['league_id'],
            'season' => $record['season'],
            'team_id' => $record['team']['id'],
            'rank' => $record['rank'],
            'points' => $record['points'] ?? null,
            'goals_diff' => $record['goalsDiff'] ?? null,
            'form' => $record['form'] ?? null,
            'group_name' => $record['group'] ?? null,
            'matches_played' => $record['all']['played'] ?? null,
            'wins' => $record['all']['win'] ?? null,
            'draws' => $record['all']['draw'] ?? null,
            'losses' => $record['all']['lose'] ?? null,
            'goals_for' => $record['all']['goals']['for'] ?? null,
            'goals_against' => $record['all']['goals']['against'] ?? null,
        ];

        $this->insertOrUpdate('standings', $data, ['league_id', 'season', 'team_id']);
    }
}
