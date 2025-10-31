<?php

namespace App\Repositories;

class TeamRepository extends BaseRepository
{
    public function upsert(array $team): void
    {
        $data = [
            'team_id' => $team['id'],
            'name' => $team['name'],
            'country' => $team['country'] ?? null,
            'founded' => $team['founded'] ?? null,
            'logo' => $team['logo'] ?? null,
            'venue' => $team['venue']['name'] ?? null,
            'short_code' => $team['code'] ?? null,
        ];

        $this->insertOrUpdate('teams', $data, ['team_id']);
    }
}
