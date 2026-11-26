<?php

declare(strict_types=1);

$repo = dirname(__DIR__, 2);
if (is_file($repo . '/vendor/autoload.php')) {
    require_once $repo . '/vendor/autoload.php';
}

spl_autoload_register(static function (string $class) use ($repo): void {
    foreach (['KlinikaID\\Data\\' => '/database/lib/', 'KlinikaID\\' => '/src/'] as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $repo . $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
            return;
        }
    }
});
