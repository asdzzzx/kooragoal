<?php

namespace App\Repositories;

class EventRepository extends BaseRepository
{
    public function upsert(array $event): void
    {
        $data = [
            'fixture_id' => $event['fixture'],
            'time_elapsed' => $event['time']['elapsed'] ?? null,
            'time_extra' => $event['time']['extra'] ?? null,
            'team_id' => $event['team']['id'] ?? null,
            'player_id' => $event['player']['id'] ?? null,
            'assist_id' => $event['assist']['id'] ?? null,
            'type' => $event['type'] ?? null,
            'detail' => $event['detail'] ?? null,
            'comments' => $event['comments'] ?? null,
        ];

        $this->insertOrUpdate('events', $data, ['fixture_id', 'time_elapsed', 'time_extra', 'team_id', 'player_id', 'type', 'detail']);
    }
}
