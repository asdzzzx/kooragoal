<?php

namespace App\Services;

use App\Repositories\EventRepository;
use App\Repositories\FixtureRepository;
use App\Repositories\LeagueRepository;
use App\Repositories\LineupRepository;
use App\Repositories\PlayerRepository;
use App\Repositories\ScorerRepository;
use App\Repositories\StandingRepository;
use App\Repositories\StatisticRepository;
use App\Repositories\TeamRepository;
use App\Support\Config;
use App\Support\Logger;

class DataIngestor
{
    private ApiFootballClient $client;
    private FixtureRepository $fixtures;
    private EventRepository $events;
    private StatisticRepository $statistics;
    private LineupRepository $lineups;
    private StandingRepository $standings;
    private ScorerRepository $scorers;
    private TeamRepository $teams;
    private PlayerRepository $players;
    private LeagueRepository $leagues;

    public function __construct()
    {
        $this->client = new ApiFootballClient();
        $this->fixtures = new FixtureRepository();
        $this->events = new EventRepository();
        $this->statistics = new StatisticRepository();
        $this->lineups = new LineupRepository();
        $this->standings = new StandingRepository();
        $this->scorers = new ScorerRepository();
        $this->teams = new TeamRepository();
        $this->players = new PlayerRepository();
        $this->leagues = new LeagueRepository();
    }

    public function syncConfiguredLeagues(): void
    {
        $this->leagues->syncConfiguredLeagues(Config::get('leagues'));
    }

    public function syncDailyFixtures(?string $date = null): void
    {
        $this->syncConfiguredLeagues();
        $date = $date ?: date('Y-m-d');
        $response = $this->client->get('fixtures', ['date' => $date], 60);

        foreach ($response['response'] ?? [] as $fixture) {
            $this->fixtures->upsert($fixture);
        }

        Logger::info('Daily fixtures synced', ['count' => count($response['response'] ?? [])]);
    }

    public function syncLiveFixtures(): void
    {
        $response = $this->client->get('fixtures', ['live' => 'all'], 10);

        foreach ($response['response'] ?? [] as $fixture) {
            $this->fixtures->upsert($fixture);
        }

        Logger::info('Live fixtures synced', ['count' => count($response['response'] ?? [])]);
    }

    public function syncEventsAndStats(): void
    {
        $activeFixtures = $this->fixtures->listActiveFixtures();

        foreach ($activeFixtures as $fixtureId) {
            $this->syncEvents($fixtureId);
            $this->syncStatistics($fixtureId);
        }

        Logger::info('Events and statistics synced', ['fixtures' => count($activeFixtures)]);
    }

    public function syncEvents(int $fixtureId): void
    {
        $response = $this->client->get('fixtures/events', ['fixture' => $fixtureId], 15);
        foreach ($response['response'] ?? [] as $event) {
            $event['fixture'] = $fixtureId;
            $this->events->upsert($event);
        }
    }

    public function syncStatistics(int $fixtureId): void
    {
        $response = $this->client->get('fixtures/statistics', ['fixture' => $fixtureId], 15);
        foreach ($response['response'] ?? [] as $stat) {
            $this->statistics->upsert($fixtureId, $stat);
        }
    }

    public function syncLineups(): void
    {
        $fixtures = $this->fixtures->listUpcomingWithLineupWindow();
        foreach ($fixtures as $fixture) {
            $response = $this->client->get('fixtures/lineups', ['fixture' => $fixture['fixture_id']], 120);
            foreach ($response['response'] ?? [] as $lineup) {
                $this->lineups->upsert($fixture['fixture_id'], $lineup);
            }
        }

        Logger::info('Lineups synced', ['fixtures' => count($fixtures)]);
    }

    public function syncStandingsAndScorers(): void
    {
        foreach (Config::get('leagues') as $league) {
            if (empty($league['api_id'])) {
                continue;
            }

            $params = ['league' => $league['api_id'], 'season' => $league['season']];
            $standingsResponse = $this->client->get('standings', $params, 3600);
            foreach ($standingsResponse['response'][0]['league']['standings'][0] ?? [] as $record) {
                $record['league_id'] = $league['api_id'];
                $record['season'] = $league['season'];
                $this->standings->upsert($record);
            }

            $scorersResponse = $this->client->get('players/topscorers', $params, 3600);
            foreach ($scorersResponse['response'] ?? [] as $index => $record) {
                $record['league_id'] = $league['api_id'];
                $record['season'] = $league['season'];
                $record['rank'] = $index + 1;
                $this->scorers->upsert($record);
            }
        }
    }

    public function syncTeamsAndPlayers(): void
    {
        foreach (Config::get('leagues') as $league) {
            if (empty($league['api_id'])) {
                continue;
            }

            $teamsResponse = $this->client->get('teams', ['league' => $league['api_id'], 'season' => $league['season']], 86400);
            foreach ($teamsResponse['response'] ?? [] as $team) {
                $this->teams->upsert($team['team']);
            }

            $page = 1;
            do {
                $playersResponse = $this->client->get('players', [
                    'league' => $league['api_id'],
                    'season' => $league['season'],
                    'page' => $page,
                ], 86400);

                $players = $playersResponse['response'] ?? [];
                foreach ($players as $playerRecord) {
                    $this->players->upsert($playerRecord['player'], $playerRecord['statistics'][0]['team']['id'] ?? null);
                }

                $page++;
                $totalPages = $playersResponse['paging']['total'] ?? $page - 1;
            } while ($page <= $totalPages);
        }
    }
}
