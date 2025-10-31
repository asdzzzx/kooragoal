<?php

require_once __DIR__ . '/../../bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ensure_admin(string $redirectPath = '/admin/index.php'): void
{
    if (empty($_SESSION['admin_authenticated'])) {
        header('Location: ' . $redirectPath);
        exit;
    }
}
