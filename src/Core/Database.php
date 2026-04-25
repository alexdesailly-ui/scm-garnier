<?php

declare(strict_types=1);

namespace SCM\Core;

final class Database
{
    private ?\PDO $pdo = null;
    private string $dsn;
    private string $user;
    private string $pass;
    private string $timezone;

    public function __construct(
        string $host,
        string $name,
        string $user,
        string $pass,
        string $charset = 'utf8mb4',
        string $timezone = '+02:00',
    ) {
        $this->dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $name, $charset);
        $this->user = $user;
        $this->pass = $pass;
        $this->timezone = $timezone;
    }

    public static function fromConfig(Config $config): self
    {
        return new self(
            $config->get('DB_HOST', 'localhost'),
            $config->get('DB_NAME', ''),
            $config->get('DB_USER', ''),
            $config->get('DB_PASS', ''),
            $config->get('DB_CHARSET', 'utf8mb4'),
            $config->get('DB_TIMEZONE', '+02:00'),
        );
    }

    public function pdo(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new \PDO($this->dsn, $this->user, $this->pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            if ($this->timezone !== '' && preg_match('/^[+-]\d{2}:\d{2}$/', $this->timezone)) {
                $this->pdo->exec("SET time_zone = '{$this->timezone}'");
            }
        }

        return $this->pdo;
    }

    public function reconnect(): void
    {
        $this->pdo = null;
    }

    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }
}
