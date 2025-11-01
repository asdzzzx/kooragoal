<?php
use Kooragoal\Services\Database;

require_once __DIR__ . '/../src/Services/Database.php';

function make_database_connection(): Database
{
    $config = require __DIR__ . '/config.php';
    return new Database($config['db']);
}
