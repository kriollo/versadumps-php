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
    public function vd(array $data = []): void
    {
        // Elige el frame apropiado del backtrace: saltar el wrapper global `vd` y esta clase/helpers
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $frame = [];
        $selfPath = realpath(__FILE__);
        $helpersPath = realpath(__DIR__ . '/helpers.php');

        $selected = null;
        for ($i = 1, $len = count($bt); $i < $len; $i++) {
            $f = $bt[$i];
            $file = isset($f['file']) ? (realpath($f['file']) ?: $f['file']) : null;

            // saltar si el frame pertenece a esta clase o al helper global
            if ($file !== null) {
                if ($selfPath !== false && $file === $selfPath) {
                    continue;
                }
                if ($helpersPath !== false && $file === $helpersPath) {
                    continue;
                }
            }

            // saltar wrappers con función 'vd'
            if (isset($f['function']) && $f['function'] === 'vd') {
                continue;
            }

            $selected = $f;
            break;
        }

        if ($selected !== null) {
            $frame = [
                'file' => $selected['file'] ?? null,
                'line' => $selected['line'] ?? null,
                'function' => isset($selected['class']) ? ($selected['class'] . '::' . ($selected['function'] ?? '')) : ($selected['function'] ?? null),
            ];
        }

        // Normalizar/convertir objetos en el contexto
        $normalized = [];
        foreach ($data as $k => $v) {
            $normalized[] = self::normalizeValue($v);
        }

        $payload = [
            'context' => $normalized,
            'frame' => $frame,
        ];

        self::post(sprintf('http://%s:%d/data', $this->host, $this->port), json_encode($payload));
    }

    /** Normaliza un valor para envío: soporta toArray(), JsonSerializable y objetos simples. */
    private static function normalizeValue(mixed $value): mixed
    {
        if (is_object($value)) {
            // Si el objeto define toArray(), úsalo
            if (method_exists($value, 'toArray')) {
                try {
                    return $value->toArray();
                } catch (\Throwable $e) {
                    // fallthrough
                }
            }

            // Si implementa JsonSerializable, use jsonSerialize
            if ($value instanceof \JsonSerializable) {
                try {
                    return $value->jsonSerialize();
                } catch (\Throwable $e) {
                    // fallthrough
                }
            }

            // Último recurso: convertir propiedades públicas
            $vars = get_object_vars($value);
            if (!empty($vars)) {
                return $vars;
            }

            // Si no hay propiedades públicas, serializar a string como último recurso
            return (string) $value;
        }

        // arrays o scalars se devuelven tal cual
        return $value;
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
        \Versadumps\Versadumps\VersaDumps::getInstance()->vd($vars);
    }
}
