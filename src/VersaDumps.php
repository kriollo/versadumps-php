<?php

namespace Versadumps\Versadumps;

use Exception;
use Symfony\Component\Yaml\Yaml;

class VersaDumps
{
    private string $host;

    private int $port;

    /** @var self|null */
    private static ?self $instance = null;

    /**
     * Constructor. Se deja público por compatibilidad, pero usar el singleton via getInstance() es preferible.
     */
    public function __construct()
    {
        if (self::$instance !== null) {
            @trigger_error('Instanciación directa de VersaDumps está desaprobada. Usa VersaDumps::getInstance() en su lugar.', E_USER_DEPRECATED);
        }
        // mantener referencia singleton a la última instancia creada
        self::$instance = $this;
        // Buscar el archivo de configuración en varias rutas comunes.
        $candidates = [
            getcwd() . '/versadumps.yml',                    // working dir del proceso
            __DIR__ . '/../versadumps.yml',                  // raíz del paquete
            __DIR__ . '/../../versadumps.yml',               // posible ruta cuando está en vendor/<pkg>/src
            dirname(__DIR__, 2) . '/versadumps.yml',         // subir más niveles
            // buscar en vendor del proyecto llamador
            getcwd() . '/vendor/versadumps-php/versadumps-php/versadumps.yml',
            getcwd() . '/vendor/versadumps-php/versadumps-php/src/versadumps.yml',
        ];

        $configFile = null;
        foreach ($candidates as $c) {
            if (file_exists($c)) {
                $configFile = $c;
                break;
            }
        }

        if ($configFile === null) {
            throw new Exception("El archivo de configuración 'versadumps.yml' no se encuentra. Ejecuta 'composer run-script versadumps-init' o php vendor/bin/versadumps-init para crearlo.");
        }

        $config = Yaml::parseFile($configFile);
        $this->host = $config['host'] ?? '127.0.0.1';
        $this->port = $config['port'] ?? 9191;
    }

    /** Obtener la instancia singleton */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** Dump variádico de datos */
    public function vd(...$data): void
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

        self::post("http://{$this->host}:{$this->port}/data", json_encode($payload));
    }

    private static function post(string $url, string $body): bool | string
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
}

// función global en espacio de nombres global. Se registra sólo si no existe.
if (!\function_exists('vd')) {
    function vd(...$vars)
    {
        \Versadumps\Versadumps\VersaDumps::getInstance()->vd(...$vars);
    }
}
