<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/fixtures-functions.php';

use Kooragoal\Services\Database;

/** @var Kooragoal\Services\Container $container */
$db = $container->get(Database::class);
$leagueId = (int) ($_GET['league'] ?? 0);

$fixtures = kooragoalFetchLeagueFixtures($db, $leagueId);

kooragoalRenderLeagueFixturesList($fixtures);
