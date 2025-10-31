<?php

namespace Kooragoal\Services\Security;

use Kooragoal\Services\Database;
use PDO;
use RuntimeException;

class AuthManager
{
    private Database $db;
    private array $config;

    public function __construct(Database $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
        $this->startSession();
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->config['session_name']);
            session_set_cookie_params([
                'lifetime' => $this->config['session_lifetime'],
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public function attempt(string $username, string $password): bool
    {
        $admin = $this->db->fetch('SELECT * FROM admins WHERE username = :username', ['username' => $username]);
        if (!$admin) {
            $this->registerFailedAttempt($username);
            return false;
        }

        if ($this->isLocked($username)) {
            throw new RuntimeException('Account locked. Try again later.');
        }

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['last_activity'] = time();
            $this->clearAttempts($username);
            return true;
        }

        $this->registerFailedAttempt($username);
        return false;
    }

    public function check(): bool
    {
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }

        if (!isset($_SESSION['last_activity']) || time() - $_SESSION['last_activity'] > $this->config['session_lifetime']) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
        }
        session_destroy();
    }

    private function registerFailedAttempt(string $username): void
    {
        $this->db->execute('INSERT INTO logs (type, message, created_at) VALUES (:type, :message, NOW())', [
            'type' => 'auth_failed',
            'message' => sprintf('Failed login attempt for %s', $username),
        ]);

        $this->db->execute(
            'INSERT INTO admin_login_attempts (username, attempts, last_attempt) VALUES (:username, 1, NOW())
            ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()',
            ['username' => $username]
        );
    }

    private function clearAttempts(string $username): void
    {
        $this->db->execute('DELETE FROM admin_login_attempts WHERE username = :username', ['username' => $username]);
    }

    private function isLocked(string $username): bool
    {
        $attempt = $this->db->fetch('SELECT * FROM admin_login_attempts WHERE username = :username', ['username' => $username]);
        if (!$attempt) {
            return false;
        }

        if ($attempt['attempts'] >= $this->config['lockout_threshold']) {
            $lastAttempt = strtotime($attempt['last_attempt']);
            return (time() - $lastAttempt) < ($this->config['lockout_minutes'] * 60);
        }

        return false;
    }
}
