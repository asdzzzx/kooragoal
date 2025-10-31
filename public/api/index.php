<?php

require_once __DIR__ . '/../../bootstrap.php';

use App\Security\Auth;
use App\Security\RateLimiter;
use App\Support\Config;
use App\Support\Database;
use App\Support\Logger;

$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$host = parse_url($origin, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? '');
$domain = sanitize_domain($host);
$whitelist = array_map('sanitize_domain', Config::get('app.domain_whitelist'));

if ($host && !in_array($domain, $whitelist, true)) {
    response_json(['error' => 'Forbidden origin'], 403);
    exit;
}

$token = get_bearer_token();
if (!Auth::verifyToken($token)) {
    response_json(['error' => 'Unauthorized'], 401);
    exit;
}

$limiterConfig = Config::get('security.api_rate_limit');
$limiter = new RateLimiter('public-api', $limiterConfig['max_requests'], $limiterConfig['per_minutes']);
$identity = $token ?: ($_SERVER['REMOTE_ADDR'] ?? 'guest');

if (!$limiter->hit($identity)) {
    response_json(['error' => 'Too many requests'], 429);
    exit;
}

$pdo = Database::getConnection();
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api#', '', $path) ?: '/';

header('Access-Control-Allow-Origin: ' . ($origin ?: Config::get('app.url')));
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET');

if (request_method() === 'OPTIONS') {
    exit;
}

try {
    switch (true) {
        case $path === '/' || $path === '':
            response_json([
                'message' => 'Kooragoal API',
                'endpoints' => [
                    '/api/leagues',
                    '/api/fixtures/today',
                    '/api/fixtures/live',
                    '/api/standings/{league_id}',
                    '/api/scorers/{league_id}',
                    '/api/events/{fixture_id}',
                    '/api/stats/{fixture_id}',
                    '/api/lineup/{fixture_id}',
                ],
            ]);
            break;
        case $path === '/leagues':
            $stmt = $pdo->query('SELECT api_id as id, name, country, season, type FROM leagues ORDER BY name');
            response_json(['data' => $stmt->fetchAll()]);
            break;
        case $path === '/fixtures/today':
            $stmt = $pdo->prepare('SELECT * FROM fixtures WHERE DATE(fixture_date) = :date ORDER BY fixture_date');
            $stmt->execute([':date' => date('Y-m-d')]);
            response_json(['data' => $stmt->fetchAll()]);
            break;
        case $path === '/fixtures/live':
            $stmt = $pdo->query("SELECT * FROM fixtures WHERE status_short IN ('1H','2H','ET','P','BT','INT','LIVE')");
            response_json(['data' => $stmt->fetchAll()]);
            break;
        case preg_match('#^/standings/(\d+)$#', $path, $matches):
            $stmt = $pdo->prepare('SELECT * FROM standings WHERE league_id = :league ORDER BY rank ASC');
            $stmt->execute([':league' => (int)$matches[1]]);
            response_json(['data' => $stmt->fetchAll()]);
            break;
        case preg_match('#^/scorers/(\d+)$#', $path, $matches):
            $stmt = $pdo->prepare('SELECT * FROM scorers WHERE league_id = :league ORDER BY rank ASC');
            $stmt->execute([':league' => (int)$matches[1]]);
            response_json(['data' => $stmt->fetchAll()]);
            break;
        case preg_match('#^/events/(\d+)$#', $path, $matches):
            $stmt = $pdo->prepare('SELECT * FROM events WHERE fixture_id = :fixture ORDER BY time_elapsed, time_extra');
            $stmt->execute([':fixture' => (int)$matches[1]]);
            response_json(['data' => $stmt->fetchAll()]);
            break;
        case preg_match('#^/stats/(\d+)$#', $path, $matches):
            $stmt = $pdo->prepare('SELECT * FROM statistics WHERE fixture_id = :fixture');
            $stmt->execute([':fixture' => (int)$matches[1]]);
            response_json(['data' => $stmt->fetchAll()]);
            break;
        case preg_match('#^/lineup/(\d+)$#', $path, $matches):
            $stmt = $pdo->prepare('SELECT * FROM lineups WHERE fixture_id = :fixture');
            $stmt->execute([':fixture' => (int)$matches[1]]);
            $results = $stmt->fetchAll();
            foreach ($results as &$lineup) {
                $lineup['starting'] = json_decode($lineup['starting'], true);
                $lineup['substitutes'] = json_decode($lineup['substitutes'], true);
            }
            response_json(['data' => $results]);
            break;
        default:
            response_json(['error' => 'Not Found'], 404);
    }
} catch (Throwable $e) {
    Logger::error('API error', ['error' => $e->getMessage(), 'path' => $path]);
    response_json(['error' => 'Server Error'], 500);
}
