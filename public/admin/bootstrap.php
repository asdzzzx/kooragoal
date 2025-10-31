<?php

require_once __DIR__ . '/../../bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ensure_admin(): void
{
    if (empty($_SESSION['admin_authenticated'])) {
        header('Location: index.php');
        exit;
    }
}
