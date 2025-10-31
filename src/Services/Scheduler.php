<?php

namespace Kooragoal\Services;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Kooragoal\Services\Updaters\UpdateManager;

class Scheduler
{
    private Database $db;
    private ApiClient $client;
    private Logger $logger;
    private UpdateManager $updates;
    private array $schedules;
    private DateTimeZone $timezone;

    public function __construct(Database $db, ApiClient $client, Logger $logger)
    {
        $this->db = $db;
        $this->client = $client;
        $this->logger = $logger;
        $this->updates = new UpdateManager($db, $client, $logger);
        $this->timezone = new DateTimeZone('UTC');
        $this->schedules = $this->bootSchedules();
    }

    public function runDueTasks(): void
    {
        foreach ($this->schedules as $name => $schedule) {
            if (isset($schedule['dynamic'])) {
                $items = $schedule['dynamic']();
                foreach ($items as $item) {
                    $contextTask = $this->contextualTaskName($name, $item);
                    if ($this->isDue($contextTask, $schedule)) {
                        $this->executeTask($schedule['handler'], $contextTask, $item);
                    }
                }
            } else {
                if ($this->isDue($name, $schedule)) {
                    $this->executeTask($schedule['handler'], $name);
                }
            }
        }
    }

    private function executeTask(callable $handler, string $taskKey, $payload = null): void
    {
        try {
            if ($payload !== null) {
                $handler($payload);
            } else {
                $handler();
            }
            $this->markExecuted($taskKey);
        } catch (\Throwable $e) {
            $this->logger->error('Scheduled task failed', ['task' => $taskKey, 'error' => $e->getMessage()]);
            $this->markExecuted($taskKey, false, $e->getMessage());
        }
    }

    private function bootSchedules(): array
    {
        return [
            'fixtures_daily' => [
                'interval' => 'P1D',
                'at_time' => '00:05',
                'handler' => function () {
                    $this->updates->updateDailyFixtures(new DateTimeImmutable('now', $this->timezone));
                },
            ],
            'fixtures_live' => [
                'interval_seconds' => 25,
                'handler' => function () {
                    $this->updates->updateLiveFixtures();
                },
            ],
            'fixture_details' => [
                'interval_seconds' => 50,
                'dynamic' => function () {
                    return $this->getLiveFixtureIds();
                },
                'handler' => function (int $fixtureId) {
                    $this->updates->updateFixtureDetails($fixtureId);
                },
            ],
            'fixture_lineups' => [
                'interval_seconds' => 300,
                'dynamic' => function () {
                    return $this->getLineupEligibleFixtures();
                },
                'handler' => function (int $fixtureId) {
                    $this->updates->updateLineups($fixtureId);
                },
            ],
            'standings_scorers' => [
                'interval_seconds' => 7200,
                'dynamic' => function () {
                    return $this->getTrackedLeagues();
                },
                'handler' => function (array $context) {
                    $this->updates->updateStandingsAndScorers($context['league_id'], $context['season']);
                },
            ],
            'teams_players' => [
                'interval' => 'P1M',
                'dynamic' => function () {
                    return $this->getTrackedLeagues();
                },
                'handler' => function (array $context) {
                    $this->updates->updateTeamsAndPlayers($context['league_id'], $context['season']);
                },
            ],
        ];
    }

    private function isDue(string $taskName, array $schedule): bool
    {
        $record = $this->db->fetch('SELECT * FROM system_updates WHERE task = :task', ['task' => $taskName]);
        $lastRun = $record ? new DateTimeImmutable($record['last_run'], $this->timezone) : null;
        $now = new DateTimeImmutable('now', $this->timezone);

        if (!$lastRun) {
            return true;
        }

        if (isset($schedule['interval_seconds'])) {
            $nextRun = $lastRun->modify('+' . $schedule['interval_seconds'] . ' seconds');
            return $now >= $nextRun;
        }

        if (isset($schedule['interval'])) {
            $nextRun = $lastRun->add(new DateInterval($schedule['interval']));
            if (isset($schedule['at_time'])) {
                $nextRun = $nextRun->setTime(...$this->parseTime($schedule['at_time']));
            }
            return $now >= $nextRun;
        }

        if (isset($schedule['at_time'])) {
            $today = new DateTimeImmutable('today', $this->timezone)->setTime(...$this->parseTime($schedule['at_time']));
            return $lastRun < $today && $now >= $today;
        }

        return false;
    }

    private function markExecuted(string $task, bool $success = true, ?string $message = null): void
    {
        $this->db->execute(
            'INSERT INTO system_updates (task, last_run, status, message)
            VALUES (:task, NOW(), :status, :message)
            ON DUPLICATE KEY UPDATE last_run = NOW(), status = VALUES(status), message = VALUES(message)',
            [
                'task' => $task,
                'status' => $success ? 'success' : 'failed',
                'message' => $message,
            ]
        );
    }

    private function contextualTaskName(string $base, $context): string
    {
        if (is_array($context)) {
            return $base . ':' . implode('-', $context);
        }
        return $base . ':' . $context;
    }

    private function getLiveFixtureIds(): array
    {
        $rows = $this->db->fetchAll("SELECT id FROM fixtures WHERE status_short IN ('1H', '2H', 'ET', 'P', 'BT', 'LIVE')");
        return array_map(fn($row) => (int) $row['id'], $rows);
    }

    private function getLineupEligibleFixtures(): array
    {
        $now = new DateTimeImmutable('now', $this->timezone);
        $rows = $this->db->fetchAll('SELECT id, FROM_UNIXTIME(timestamp) as kickoff FROM fixtures WHERE status_short = "NS"');
        $eligible = [];
        foreach ($rows as $row) {
            $kickoff = new DateTimeImmutable($row['kickoff'], $this->timezone);
            $diff = $kickoff->getTimestamp() - $now->getTimestamp();
            if ($diff <= 1800 && $diff >= 0) {
                $eligible[] = (int) $row['id'];
            }
        }
        return $eligible;
    }

    private function getTrackedLeagues(): array
    {
        $rows = $this->db->fetchAll('SELECT DISTINCT league_id as league_id, season FROM fixtures');
        return array_map(fn($row) => ['league_id' => (int)$row['league_id'], 'season' => (int)$row['season']], $rows);
    }

    private function parseTime(string $time): array
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        return [$hour, $minute];
    }
}
