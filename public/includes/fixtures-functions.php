<?php

use Kooragoal\Services\Database;

if (!function_exists('kooragoalFetchFixturesByDate')) {
    function kooragoalFetchFixturesByDate(Database $db, \DateTimeImmutable $date): array
    {
        return $db->fetchAll(
            'SELECT f.*, th.name AS home_name, ta.name AS away_name, l.name AS league_name
            FROM fixtures f
            JOIN teams th ON th.id = f.home_team_id
            JOIN teams ta ON ta.id = f.away_team_id
            JOIN leagues l ON l.id = f.league_id
            WHERE DATE(FROM_UNIXTIME(f.timestamp)) = :match_date
            ORDER BY f.timestamp ASC',
            [
                'match_date' => $date->format('Y-m-d'),
            ]
        );
    }
}

if (!function_exists('kooragoalRenderFixturesCards')) {
    function kooragoalRenderFixturesCards(array $fixtures): void
    {
        if (!$fixtures) {
            echo '<div class="col-12"><div class="alert alert-info">لا توجد مباريات مجدولة لهذا اليوم.</div></div>';
            return;
        }

        foreach ($fixtures as $fixture) {
            $homeName = htmlspecialchars($fixture['home_name'] ?? '');
            $awayName = htmlspecialchars($fixture['away_name'] ?? '');
            $leagueName = htmlspecialchars($fixture['league_name'] ?? '');
            $statusLong = htmlspecialchars($fixture['status_long'] ?? '');
            $fixtureId = (int) ($fixture['id'] ?? 0);
            $homeGoals = (int) ($fixture['goals_home'] ?? 0);
            $awayGoals = (int) ($fixture['goals_away'] ?? 0);
            $timestamp = isset($fixture['timestamp']) ? (int) $fixture['timestamp'] : null;
            $kickoffTime = $timestamp ? gmdate('H:i', $timestamp) : '';

            echo '<div class="col-md-6">';
            echo '    <div class="card shadow-sm h-100">';
            echo '        <div class="card-body">';
            echo '            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">';
            echo '                <h5 class="card-title mb-0">' . $leagueName . '</h5>';
            if ($kickoffTime) {
                echo '                <span class="badge bg-secondary">' . htmlspecialchars($kickoffTime) . '</span>';
            }
            echo '            </div>';
            echo '            <p class="card-text mt-3">';
            echo '                ' . $homeName;
            echo '                <strong class="mx-2">' . $homeGoals . ' - ' . $awayGoals . '</strong>';
            echo '                ' . $awayName;
            echo '            </p>';
            echo '            <p class="card-text text-muted">الحالة: ' . $statusLong . '</p>';
            echo '            <a href="/match/' . $fixtureId . '" class="btn btn-outline-primary btn-sm">التفاصيل</a>';
            echo '        </div>';
            echo '    </div>';
            echo '</div>';
        }
    }
}

if (!function_exists('kooragoalFetchLeagueFixtures')) {
    function kooragoalFetchLeagueFixtures(Database $db, int $leagueId): array
    {
        return $db->fetchAll(
            'SELECT f.*, th.name AS home_name, ta.name AS away_name
            FROM fixtures f
            JOIN teams th ON th.id = f.home_team_id
            JOIN teams ta ON ta.id = f.away_team_id
            WHERE f.league_id = :league
            ORDER BY f.timestamp DESC
            LIMIT 50',
            ['league' => $leagueId]
        );
    }
}

if (!function_exists('kooragoalRenderLeagueFixturesList')) {
    function kooragoalRenderLeagueFixturesList(array $fixtures): void
    {
        if (!$fixtures) {
            echo '<div class="list-group-item">لا توجد مباريات متاحة.</div>';
            return;
        }

        foreach ($fixtures as $fixture) {
            $fixtureId = (int) ($fixture['id'] ?? 0);
            $homeName = htmlspecialchars($fixture['home_name'] ?? '');
            $awayName = htmlspecialchars($fixture['away_name'] ?? '');
            $statusShort = htmlspecialchars($fixture['status_short'] ?? '');

            echo '<a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="/match/' . $fixtureId . '">';
            echo '    <span>' . $homeName . ' ضد ' . $awayName . '</span>';
            echo '    <span class="badge bg-secondary">' . $statusShort . '</span>';
            echo '</a>';
        }
    }
}
