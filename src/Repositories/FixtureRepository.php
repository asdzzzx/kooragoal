<?php

namespace App\Repositories;

class FixtureRepository extends BaseRepository
{
    public function upsert(array $fixture): void
    {
        $league = $fixture['league'];
        $teams = $fixture['teams'];
        $goals = $fixture['goals'] ?? ['home' => null, 'away' => null];
        $score = $fixture['score'] ?? [];
        $status = $fixture['fixture']['status'] ?? [];
        $fixtureInfo = $fixture['fixture'];

        $data = [
            'fixture_id' => $fixtureInfo['id'],
            'league_id' => $league['id'],
            'season' => $league['season'],
            'round' => $league['round'] ?? null,
            'status_short' => $status['short'] ?? null,
            'status_long' => $status['long'] ?? null,
            'fixture_timestamp' => $fixtureInfo['timestamp'] ?? null,
            'fixture_date' => date('Y-m-d H:i:s', $fixtureInfo['timestamp'] ?? time()),
            'referee' => $fixtureInfo['referee'] ?? null,
            'venue' => $fixtureInfo['venue']['name'] ?? null,
            'home_team_id' => $teams['home']['id'],
            'home_team_name' => $teams['home']['name'],
            'home_team_logo' => $teams['home']['logo'] ?? null,
            'away_team_id' => $teams['away']['id'],
            'away_team_name' => $teams['away']['name'],
            'away_team_logo' => $teams['away']['logo'] ?? null,
            'goals_home' => $goals['home'],
            'goals_away' => $goals['away'],
            'halftime_home' => $score['halftime']['home'] ?? null,
            'halftime_away' => $score['halftime']['away'] ?? null,
            'fulltime_home' => $score['fulltime']['home'] ?? null,
            'fulltime_away' => $score['fulltime']['away'] ?? null,
            'extratime_home' => $score['extratime']['home'] ?? null,
            'extratime_away' => $score['extratime']['away'] ?? null,
            'penalty_home' => $score['penalty']['home'] ?? null,
            'penalty_away' => $score['penalty']['away'] ?? null,
        ];

        $this->insertOrUpdate('fixtures', $data, ['fixture_id']);
    }

    public function listActiveFixtures(): array
    {
        $stmt = $this->db->query("SELECT fixture_id FROM fixtures WHERE status_short IN ('1H','2H','ET','P','BT','INT','LIVE')");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }

    public function listUpcomingWithLineupWindow(): array
    {
        $stmt = $this->db->prepare("SELECT fixture_id, fixture_timestamp FROM fixtures WHERE fixture_timestamp BETWEEN :from AND :to");
        $stmt->execute([
            ':from' => time() - 900,
            ':to' => time() + 1800,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
