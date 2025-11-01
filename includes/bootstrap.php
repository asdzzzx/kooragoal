<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Kooragoal\Services\Scheduler;
use Kooragoal\Services\Security\AuthManager;

/** @var Kooragoal\Services\Container $container */
$scheduler = $container->get(Scheduler::class);
$scheduler->runDueTasks();

// Ensure the authentication service is always initialised for session handling
$auth = $container->get(AuthManager::class);
