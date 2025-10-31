<?php

namespace App\Security;

class Auth
{
    public static function verifyAdmin(string $username, string $password): bool
    {
        $config = config('auth.admin');
        return $username === $config['username'] && password_verify($password, $config['password_hash']);
    }

    public static function verifyToken(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        foreach (config('auth.tokens') as $record) {
            if (password_verify($token, $record['hash'])) {
                return true;
            }
        }

        return false;
    }
}
