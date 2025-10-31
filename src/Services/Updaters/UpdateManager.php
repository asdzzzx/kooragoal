<?php

namespace Kooragoal\Services\Updaters;

use DateTimeImmutable;
use Kooragoal\Services\ApiClient;
use Kooragoal\Services\Database;
use Kooragoal\Services\Logger;

class UpdateManager
{
    private Database $db;
    private ApiClient $client;
    private Logger $logger;

    public function __construct(Database $db, ApiClient $client, Logger $logger)
    {
        $this->db = $db;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function updateDailyFixtures(DateTimeImmutable $date): void
    {
        $formatted = $date->format('Y-m-d');
        $fixtures = $this->client->get('/fixtures', ['date' => $formatted]);
        $this->db->transaction(function (Database $db) use ($fixtures) {
            foreach ($fixtures as $fixture) {
                $this->persistFixture($db, $fixture);
            }
        });
        $this->logger->info('Daily fixtures updated', ['date' => $formatted, 'count' => count($fixtures)]);
    }

    public function updateLiveFixtures(): void
    {
        $fixtures = $this->client->get('/fixtures', ['live' => 'all']);
        $this->db->transaction(function (Database $db) use ($fixtures) {
            foreach ($fixtures as $fixture) {
                $this->persistFixture($db, $fixture);
            }
        });
        $this->logger->info('Live fixtures refreshed', ['count' => count($fixtures)]);
    }

    public function updateFixtureDetails(int $fixtureId): void
    {
        $statistics = $this->client->get('/fixtures/statistics', ['fixture' => $fixtureId]);
        $events = $this->client->get('/fixtures/events', ['fixture' => $fixtureId]);

        $this->db->transaction(function (Database $db) use ($fixtureId, $statistics, $events) {
            $db->execute('DELETE FROM statistics WHERE fixture_id = :fixture', ['fixture' => $fixtureId]);
            foreach ($statistics as $entry) {
                $db->execute(
                    'INSERT INTO statistics (fixture_id, team_id, type, value) VALUES (:fixture_id, :team_id, :type, :value)',
                    [
                        'fixture_id' => $fixtureId,
                        'team_id' => $entry['team']['id'],
                        'type' => $entry['type'],
                        'value' => json_encode($entry['statistics'], JSON_UNESCAPED_UNICODE),
                    ]
                );
            }

            $db->execute('DELETE FROM events WHERE fixture_id = :fixture', ['fixture' => $fixtureId]);
            foreach ($events as $event) {
                $db->execute(
                    'INSERT INTO events (fixture_id, time_elapsed, team_id, player_id, type, detail, comments)
                    VALUES (:fixture_id, :time_elapsed, :team_id, :player_id, :type, :detail, :comments)',
                    [
                        'fixture_id' => $fixtureId,
                        'time_elapsed' => $event['time']['elapsed'],
                        'team_id' => $event['team']['id'],
                        'player_id' => $event['player']['id'] ?? null,
                        'type' => $event['type'],
                        'detail' => $event['detail'],
                        'comments' => $event['comments'] ?? null,
                    ]
                );
            }
        });

        $this->logger->info('Fixture statistics/events updated', ['fixture' => $fixtureId]);
    }

    public function updateLineups(int $fixtureId): void
    {
        $lineups = $this->client->get('/fixtures/lineups', ['fixture' => $fixtureId]);
        $this->db->transaction(function (Database $db) use ($fixtureId, $lineups) {
            $db->execute('DELETE FROM lineups WHERE fixture_id = :fixture', ['fixture' => $fixtureId]);
            foreach ($lineups as $lineup) {
                $db->execute(
                    'INSERT INTO lineups (fixture_id, team_id, formation, start_xi, substitutes)
                    VALUES (:fixture_id, :team_id, :formation, :start_xi, :substitutes)',
                    [
                        'fixture_id' => $fixtureId,
                        'team_id' => $lineup['team']['id'],
                        'formation' => $lineup['formation'],
                        'start_xi' => json_encode($lineup['startXI'], JSON_UNESCAPED_UNICODE),
                        'substitutes' => json_encode($lineup['substitutes'], JSON_UNESCAPED_UNICODE),
                    ]
                );
            }
        });
        $this->logger->info('Lineups updated', ['fixture' => $fixtureId]);
    }

    public function updateStandingsAndScorers(int $leagueId, int $season): void
    {
        $standings = $this->client->get('/standings', ['league' => $leagueId, 'season' => $season]);
        $scorers = $this->client->get('/players/topscorers', ['league' => $leagueId, 'season' => $season]);
        $this->db->transaction(function (Database $db) use ($leagueId, $season, $standings, $scorers) {
            $db->execute('DELETE FROM standings WHERE league_id = :league AND season = :season', ['league' => $leagueId, 'season' => $season]);
            foreach ($standings as $entry) {
                foreach ($entry['league']['standings'][0] ?? [] as $row) {
                    $db->execute(
                        'INSERT INTO standings (league_id, season, team_id, rank_position, points, goals_diff, stats)
                        VALUES (:league_id, :season, :team_id, :rank_position, :points, :goals_diff, :stats)',
                        [
                            'league_id' => $leagueId,
                            'season' => $season,
                            'team_id' => $row['team']['id'],
                            'rank_position' => $row['rank'],
                            'points' => $row['points'],
                            'goals_diff' => $row['goalsDiff'],
                            'stats' => json_encode($row, JSON_UNESCAPED_UNICODE),
                        ]
                    );
                }
            }

            $db->execute('DELETE FROM scorers WHERE league_id = :league AND season = :season', ['league' => $leagueId, 'season' => $season]);
            foreach ($scorers as $scorer) {
                $db->execute(
                    'INSERT INTO scorers (league_id, season, player_id, team_id, goals, assists, stats)
                    VALUES (:league, :season, :player, :team, :goals, :assists, :stats)',
                    [
                        'league' => $leagueId,
                        'season' => $season,
                        'player' => $scorer['player']['id'],
                        'team' => $scorer['statistics'][0]['team']['id'],
                        'goals' => $scorer['statistics'][0]['goals']['total'] ?? 0,
                        'assists' => $scorer['statistics'][0]['goals']['assists'] ?? 0,
                        'stats' => json_encode($scorer, JSON_UNESCAPED_UNICODE),
                    ]
                );
            }
        });
        $this->logger->info('Standings and scorers updated', ['league' => $leagueId, 'season' => $season]);
    }

    public function updateTeamsAndPlayers(int $leagueId, int $season): void
    {
        $teams = $this->client->get('/teams', ['league' => $leagueId, 'season' => $season]);
        $this->db->transaction(function (Database $db) use ($teams) {
            foreach ($teams as $team) {
                $db->execute(
                    'REPLACE INTO teams (id, name, code, country, founded, logo, venue)
                    VALUES (:id, :name, :code, :country, :founded, :logo, :venue)',
                    [
                        'id' => $team['team']['id'],
                        'name' => $team['team']['name'],
                        'code' => $team['team']['code'],
                        'country' => $team['team']['country'],
                        'founded' => $team['team']['founded'],
                        'logo' => $team['team']['logo'],
                        'venue' => json_encode($team['venue'], JSON_UNESCAPED_UNICODE),
                    ]
                );
            }
        });

        $players = $this->client->get('/players', ['league' => $leagueId, 'season' => $season]);
        $this->db->transaction(function (Database $db) use ($players) {
            foreach ($players as $entry) {
                foreach ($entry['players'] as $player) {
                    $db->execute(
                        'REPLACE INTO players (id, team_id, name, firstname, lastname, age, nationality, height, weight, photo, stats)
                        VALUES (:id, :team_id, :name, :firstname, :lastname, :age, :nationality, :height, :weight, :photo, :stats)',
                        [
                            'id' => $player['id'],
                            'team_id' => $entry['team']['id'],
                            'name' => $player['name'],
                            'firstname' => $player['firstname'],
                            'lastname' => $player['lastname'],
                            'age' => $player['age'],
                            'nationality' => $player['nationality'],
                            'height' => $player['height'],
                            'weight' => $player['weight'],
                            'photo' => $player['photo'],
                            'stats' => json_encode($player['statistics'], JSON_UNESCAPED_UNICODE),
                        ]
                    );
                }
            }
        });

        $this->logger->info('Teams and players updated', ['league' => $leagueId, 'season' => $season]);
    }

    private function persistFixture(Database $db, array $fixture): void
    {
        $fixtureInfo = $fixture['fixture'];
        $leagueInfo = $fixture['league'];
        $teamsInfo = $fixture['teams'];
        $goals = $fixture['goals'];

        $db->execute(
            'REPLACE INTO leagues (id, name, country, logo, flag, season) VALUES (:id, :name, :country, :logo, :flag, :season)',
            [
                'id' => $leagueInfo['id'],
                'name' => $leagueInfo['name'],
                'country' => $leagueInfo['country'],
                'logo' => $leagueInfo['logo'],
                'flag' => $leagueInfo['flag'],
                'season' => $leagueInfo['season'],
            ]
        );

        foreach (['home', 'away'] as $side) {
            $team = $teamsInfo[$side];
            $db->execute(
                'REPLACE INTO teams (id, name, code, country, founded, logo, venue)
                VALUES (:id, :name, :code, :country, :founded, :logo, :venue)',
                [
                    'id' => $team['id'],
                    'name' => $team['name'],
                    'code' => $team['code'],
                    'country' => $team['country'],
                    'founded' => $team['founded'],
                    'logo' => $team['logo'],
                    'venue' => null,
                ]
            );
        }

        $db->execute(
            'REPLACE INTO fixtures (id, league_id, season, round, date, status_long, status_short, timestamp, referee, venue, home_team_id, away_team_id, goals_home, goals_away, score)
            VALUES (:id, :league_id, :season, :round, :date, :status_long, :status_short, :timestamp, :referee, :venue, :home_team_id, :away_team_id, :goals_home, :goals_away, :score)',
            [
                'id' => $fixtureInfo['id'],
                'league_id' => $leagueInfo['id'],
                'season' => $leagueInfo['season'],
                'round' => $leagueInfo['round'] ?? null,
                'date' => $fixtureInfo['date'],
                'status_long' => $fixtureInfo['status']['long'],
                'status_short' => $fixtureInfo['status']['short'],
                'timestamp' => $fixtureInfo['timestamp'],
                'referee' => $fixtureInfo['referee'],
                'venue' => json_encode($fixtureInfo['venue'], JSON_UNESCAPED_UNICODE),
                'home_team_id' => $teamsInfo['home']['id'],
                'away_team_id' => $teamsInfo['away']['id'],
                'goals_home' => $goals['home'],
                'goals_away' => $goals['away'],
                'score' => json_encode($fixture['score'], JSON_UNESCAPED_UNICODE),
            ]
        );
    }
}
