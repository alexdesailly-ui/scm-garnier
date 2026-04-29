<?php

declare(strict_types=1);

namespace SCM\Core;

use SCM\Billing\BillingService;
use SCM\Billing\Subscription;
use SCM\Model\Appointment;
use SCM\Model\Contact;
use SCM\Model\Setting;
use SCM\Model\User;
use SCM\Tenant\Tenant;

final class App
{
    private static ?self $instance = null;

    private Config $config;
    private ?Database $db = null;
    private ?Tenant $tenant = null;
    private ?Subscription $subscription = null;
    private bool $subscriptionRestricted = false;
    private bool $debug;
    private array $models = [];

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

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function tenantId(): ?int
    {
        return $this->tenant?->id;
    }

    public function appointments(): Appointment
    {
        return $this->models[Appointment::class] ??= new Appointment($this->db());
    }

    public function contacts(): Contact
    {
        return $this->models[Contact::class] ??= new Contact($this->db());
    }

    public function settings(): Setting
    {
        return $this->models[Setting::class] ??= new Setting($this->db());
    }

    public function users(): User
    {
        return $this->models[User::class] ??= new User($this->db());
    }

    public function subscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(Subscription $sub): void
    {
        $this->subscription = $sub;
    }

    public function isSubscriptionRestricted(): bool
    {
        return $this->subscriptionRestricted;
    }

    public function setSubscriptionRestricted(bool $restricted): void
    {
        $this->subscriptionRestricted = $restricted;
    }

    public function billing(): BillingService
    {
        return BillingService::create();
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
