<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/bin',
        __DIR__ . '/example',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/var',
        __DIR__ . '/app/cache',
        __DIR__ . '/example/index.php', // Skip due to local class analysis issues
    ]);

    // Configure sets
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);
};
