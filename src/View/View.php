<?php

declare(strict_types=1);

namespace SCM\View;

use SCM\Core\App;

final class View
{
    private string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = $baseDir ?? dirname(__DIR__, 2);
    }

    public function render(string $template, array $data = []): string
    {
        $file = $this->baseDir . '/' . ltrim($template, '/');

        if (!file_exists($file)) {
            throw new \RuntimeException("Template not found: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();

        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    public static function escape(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function tenantName(): string
    {
        $tenant = App::instance()->tenant();
        return $tenant !== null ? self::escape($tenant->name) : 'Cabinet Infirmier';
    }

    public static function tenantSlug(): string
    {
        $tenant = App::instance()->tenant();
        return $tenant !== null ? $tenant->slug : 'default';
    }
}
