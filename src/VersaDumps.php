<?php

namespace Versadumps\Versadumps;

use Symfony\Component\Yaml\Yaml;
use Exception;

class VersaDumps
{
    private string $host;
    private int $port;

    public function __construct()
    {
        $configFile = getcwd() . '/versadumps.yml';

        if (!file_exists($configFile)) {
            throw new Exception("El archivo de configuración 'versadumps.yml' no se encuentra. Ejecuta 'composer run-script versadumps-init' para crearlo.");
        }

        $config = Yaml::parseFile($configFile);
        $this->host = $config['host'] ?? '127.0.0.1';
        $this->port = $config['port'] ?? 9191;
    }

    private static function post(string $url, string $body): bool|string
    {
        // prefer curl when available
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }
        // fallback to file_get_contents
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $body,
                'timeout' => 1,
            ],
        ];
        $context = stream_context_create($opts);
        return @file_get_contents($url, false, $context);
    }

    public function vd($data): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        $frame = [];
        if (isset($bt[1])) {
            $caller = $bt[1];
            $frame = [
                'file' => $caller['file'] ?? null,
                'line' => $caller['line'] ?? null,
                'function' => $caller['function'] ?? null,
            ];
        }
        $payload = [
            'context' => $data,
            'frame' => $frame,
        ];

        $this->post("http://{$this->host}:{$this->port}/data", json_encode($payload));
    }
}
