<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager \$auth */
\$auth->logout();
header('Location: /admin/login.php');
exit;
