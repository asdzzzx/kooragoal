<?php
require_once __DIR__ . '/../../src/bootstrap.php';
/** @var Kooragoal\Services\Container $container */
$auth = $container->get(Kooragoal\Services\Security\AuthManager::class);
$scheduler = $container->get(Kooragoal\Services\Scheduler::class);
$scheduler->runDueTasks();
