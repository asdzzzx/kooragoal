<?php

namespace Kooragoal\Services;

use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;

class Container
{
    private array $config;
    private array $services = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $id)
    {
        if (!isset($this->services[$id])) {
            $this->services[$id] = $this->createService($id);
        }

        return $this->services[$id];
    }

    private function createService(string $id)
    {
        switch ($id) {
            case Database::class:
                return new Database($this->config['db']);
            case ApiClient::class:
                return new ApiClient($this->config['api'], $this->get(Logger::class), $this->get(Database::class));
            case Logger::class:
                return new Logger(__DIR__ . '/../../logs/app.log');
            case Scheduler::class:
                return new Scheduler($this->get(Database::class), $this->get(ApiClient::class), $this->get(Logger::class));
            case AuthManager::class:
                return new AuthManager($this->get(Database::class), $this->config['security']);
            case CsrfTokenManager::class:
                return new CsrfTokenManager($this->config['security']['csrf_token_key']);
            default:
                throw new \InvalidArgumentException("Unknown service: {$id}");
        }
    }
}
