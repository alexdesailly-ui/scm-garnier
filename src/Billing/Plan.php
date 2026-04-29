<?php

declare(strict_types=1);

namespace SCM\Billing;

use SCM\Core\App;

final class Plan
{
    public const STARTER = 'starter';
    public const PRO = 'pro';
    public const ENTERPRISE = 'enterprise';

    private static array $limits = [
        self::STARTER => [
            'appointments_per_month' => 50,
            'nurses' => 2,
            'prevention_articles' => false,
            'custom_domain' => false,
            'whatsapp' => false,
            'api_access' => false,
            'priority_support' => false,
            'encryption' => false,
            'audit_log' => false,
        ],
        self::PRO => [
            'appointments_per_month' => -1,
            'nurses' => -1,
            'prevention_articles' => true,
            'custom_domain' => true,
            'whatsapp' => true,
            'api_access' => false,
            'priority_support' => true,
            'encryption' => true,
            'audit_log' => true,
        ],
        self::ENTERPRISE => [
            'appointments_per_month' => -1,
            'nurses' => -1,
            'prevention_articles' => true,
            'custom_domain' => true,
            'whatsapp' => true,
            'api_access' => true,
            'priority_support' => true,
            'encryption' => true,
            'audit_log' => true,
        ],
    ];

    public static function priceId(string $plan): string
    {
        $cfg = App::instance()->config();

        return match ($plan) {
            self::PRO => $cfg->get('STRIPE_PRICE_PRO', ''),
            self::ENTERPRISE => $cfg->get('STRIPE_PRICE_ENTERPRISE', ''),
            default => '',
        };
    }

    public static function limit(string $plan, string $feature): mixed
    {
        return self::$limits[$plan][$feature] ?? null;
    }

    public static function canAccess(string $plan, string $feature): bool
    {
        $value = self::limit($plan, $feature);

        if (is_bool($value)) {
            return $value;
        }

        return $value !== 0;
    }

    public static function label(string $plan): string
    {
        return match ($plan) {
            self::STARTER => 'Starter',
            self::PRO => 'Pro',
            self::ENTERPRISE => 'Entreprise',
            default => $plan,
        };
    }

    public static function price(string $plan): string
    {
        return match ($plan) {
            self::STARTER => 'Gratuit',
            self::PRO => '29€/mois',
            self::ENTERPRISE => 'Sur devis',
            default => '',
        };
    }

    public static function all(): array
    {
        return [self::STARTER, self::PRO, self::ENTERPRISE];
    }

    public static function requiresPayment(string $plan): bool
    {
        return $plan !== self::STARTER;
    }
}
