<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('paths', [
        __DIR__ . '/src',
        __DIR__ . '/bin',
        __DIR__ . '/example',
    ]);

    $parameters->set('skip', [
        __DIR__ . '/vendor',
        __DIR__ . '/var',
        __DIR__ . '/app/cache',
    ]);

    // Import sets
    $containerConfigurator->import(__DIR__ . '/vendor/rector/rector/config/set/code-quality.php');
    $containerConfigurator->import(__DIR__ . '/vendor/rector/rector/config/set/type-declaration.php');
    $containerConfigurator->import(__DIR__ . '/vendor/rector/rector/config/set/coding-style.php');

    // No custom services required here.
};
