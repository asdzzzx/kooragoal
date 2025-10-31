<?php

namespace App\Repositories;

class LeagueRepository extends BaseRepository
{
    public function syncConfiguredLeagues(array $leagues): void
    {
        foreach ($leagues as $league) {
            if (empty($league['api_id'])) {
                continue;
            }

            $data = [
                'api_id' => $league['api_id'],
                'name' => $league['name'],
                'country' => $league['country'] ?? null,
                'season' => $league['season'],
                'type' => $league['type'] ?? 'league',
            ];

            $this->insertOrUpdate('leagues', $data, ['api_id']);
        }
    }
}
