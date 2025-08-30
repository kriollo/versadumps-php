<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/bin',
        __DIR__ . '/example',
    ]);

    // Ignorar vendor y cache
    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/var',
        __DIR__ . '/app/cache',
    ]);

    // === BASE RULES ===
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,   // migración progresiva hasta PHP 8.0
        SetList::CODE_QUALITY,        // incluye declare(strict_types=1)
        SetList::TYPE_DECLARATION,    // agrega tipos a funciones/props donde se puede
    ]);

    // === OPCIONAL ===
    $rectorConfig->sets([
        SetList::CODING_STYLE,
        SetList::PRIVATIZATION,
    ]);

    // === AUTOIMPORTS ===
    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
};
