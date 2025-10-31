<?php

namespace Kooragoal\Services\Security;

class CsrfTokenManager
{
    private string $sessionKey;

    public function __construct(string $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[$this->sessionKey] = $token;
        return $token;
    }

    public function getToken(): string
    {
        return $_SESSION[$this->sessionKey] ?? $this->generateToken();
    }

    public function validateToken(string $token): bool
    {
        if (!isset($_SESSION[$this->sessionKey])) {
            return false;
        }

        return hash_equals($_SESSION[$this->sessionKey], $token);
    }
}
