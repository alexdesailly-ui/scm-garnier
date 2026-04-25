<?php

declare(strict_types=1);

namespace SCM\Core;

final class App
{
    private static ?self $instance = null;

    private Config $config;
    private ?Database $db = null;
    private bool $debug;

    private function __construct(Config $config)
    {
        $this->config = $config;
        $this->debug = (bool) $config->get('APP_DEBUG', false);
    }

    public static function boot(?Config $config = null): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if ($config === null) {
            $config = Config::fromEnvFile(dirname(__DIR__, 2) . '/env.php');
        }

        self::$instance = new self($config);
        self::$instance->configureErrorReporting();

        return self::$instance;
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            return self::boot();
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function db(): Database
    {
        if ($this->db === null) {
            $this->db = Database::fromConfig($this->config);
        }

        return $this->db;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    private function configureErrorReporting(): void
    {
        if ($this->debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }
}
