<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/fixtures-functions.php';

/** @var Kooragoal\Services\Container $container */
$db = $container->get(Kooragoal\Services\Database::class);

$dateParam = $_GET['date'] ?? date('Y-m-d');
$date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateParam) ?: new \DateTimeImmutable('today');

$fixtures = kooragoalFetchFixturesByDate($db, $date);

echo '<!-- fixtures list -->';
kooragoalRenderFixturesCards($fixtures);
