<?php
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
if (!$auth->check()) {
    header('Location: /admin/login.php');
    exit;
}
