<?php

namespace App\Support;

class ModelAutoloader
{
    /**
     * Sub-namespaces inside App\Models to check for domain models.
     *
     * @var array<int, string>
     */
    protected static array $subNamespaces = [
        'Product',
        'Backend',
        'Frontend',
    ];

    /**
     * Register SPL autoloader alias bridge for domain-driven models.
     */
    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {
            if (! str_starts_with($class, 'App\\Models\\')) {
                return;
            }

            $shortName = substr($class, strlen('App\\Models\\'));
            if (str_contains($shortName, '\\')) {
                return;
            }

            foreach (self::$subNamespaces as $sub) {
                $candidate = "App\\Models\\{$sub}\\{$shortName}";
                if (class_exists($candidate)) {
                    class_alias($candidate, $class);

                    return;
                }
            }
        });
    }
}
