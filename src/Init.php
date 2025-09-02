<?php

namespace Versadumps\Versadumps;

class Init
{
    public static function run(): void
    {
        $configFile = getcwd() . '/versadumps.yml';

        if (file_exists($configFile)) {
            echo "El archivo de configuración 'versadumps.yml' ya existe.\n";

            return;
        }

        $config = [
            'host' => '127.0.0.1',
            'port' => 9191,
        ];

        $yaml = YamlParser::dump($config);

        file_put_contents($configFile, $yaml);

        echo "El archivo de configuración 'versadumps.yml' se ha creado correctamente.\n";
    }
}
