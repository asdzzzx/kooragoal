<?php

namespace App\Repositories;

class PlayerRepository extends BaseRepository
{
    public function upsert(array $player, ?int $teamId = null): void
    {
        $data = [
            'player_id' => $player['id'],
            'team_id' => $teamId,
            'name' => $player['name'],
            'nationality' => $player['nationality'] ?? null,
            'age' => $player['age'] ?? null,
            'position' => $player['position'] ?? null,
            'photo' => $player['photo'] ?? null,
            'height' => $player['height'] ?? null,
            'weight' => $player['weight'] ?? null,
        ];

        $this->insertOrUpdate('players', $data, ['player_id']);
    }
}
