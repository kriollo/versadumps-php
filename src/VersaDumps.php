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
            @trigger_error(
                'Instanciación directa de VersaDumps está desaprobada. Usa VersaDumps::getInstance() en su lugar.',
                E_USER_DEPRECATED,
            );
        }

        // mantener referencia singleton a la última instancia creada
        self::$instance = $this;
        // Buscar el archivo de configuración en varias rutas comunes.
        $candidates = [
            getcwd() . '/versadumps.yml', // working dir del proceso
            __DIR__ . '/../versadumps.yml', // raíz del paquete
            __DIR__ . '/../../versadumps.yml', // posible ruta cuando está en vendor/<pkg>/src
            dirname(__DIR__, 2) . '/versadumps.yml', // subir más niveles
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
            throw new Exception(
                "El archivo de configuración 'versadumps.yml' no se encuentra. Ejecuta 'composer run-script versadumps-init' o php vendor/bin/versadumps-init para crearlo.",
            );
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
        for ($i = 1, $len = count($bt); $i < $len; ++$i) {
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
                'function' => $selected['function'] ?? null,
                'class' => $selected['class'] ?? null,
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

    /**
     * Normaliza un valor para envío: soporta toArray(), JsonSerializable, Traversable, DateTime,
     * y convierte objetos (incluyendo propiedades protegidas/privadas) a arrays recurriendo.
     * Evita recursión mediante un mapa de objetos ya visitados.
     */
    private static function normalizeValue(mixed $value, array &$seen = []): mixed
    {
        // scalars y null
        if (is_null($value) || is_scalar($value)) {
            return $value;
        }

        // arrays: normalizar recursivamente
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = self::normalizeValue($v, $seen);
            }

            return $out;
        }

        // Traversable (Collections, iterators)
        if ($value instanceof \Traversable) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = self::normalizeValue($v, $seen);
            }

            return $out;
        }

        // DateTime: formatear
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        // Objetos
        if (is_object($value)) {
            // evitar recursión: identificar por spl_object_id
            $id = spl_object_id($value);
            if (isset($seen[$id])) {
                return ['__recursion__' => true, '__class__' => get_class($value)];
            }

            $seen[$id] = true;

            // Si define toArray(), preferirlo
            if (method_exists($value, 'toArray')) {
                try {
                    $res = $value->toArray();

                    return self::normalizeValue($res, $seen);
                } catch (\Throwable $throwable) {
                }
            }

            // JsonSerializable
            if ($value instanceof \JsonSerializable) {
                try {
                    $res = $value->jsonSerialize();

                    return self::normalizeValue($res, $seen);
                } catch (\Throwable $throwable) {
                    // fallthrough
                }
            }

            // Usar reflexión para leer propiedades públicas/protegidas/privadas
            $out = ['__class__' => get_class($value)];

            try {
                $ref = new \ReflectionObject($value);
                foreach ($ref->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $name = $prop->getName();
                    // indicar visibilidad si no es pública
                    $prefix = '';
                    if ($prop->isProtected()) {
                        $prefix = 'protected:';
                    }

                    if ($prop->isPrivate()) {
                        $prefix = 'private:' . $prop->class . ':';
                    }

                    try {
                        $val = $prop->getValue($value);
                    } catch (\ReflectionException $reflectionException) {
                        $val = null;
                    }

                    $out[$prefix . $name] = self::normalizeValue($val, $seen);
                }
            } catch (\Throwable $reflectionException) {
                // si falla reflexión, caer al cast simple
                try {
                    $cast = (array) $value;
                    foreach ($cast as $k => $v) {
                        $out[$k] = self::normalizeValue($v, $seen);
                    }
                } catch (\Throwable $_) {
                    $out['__toString'] = method_exists($value, '__toString') ? (string) $value : null;
                }
            }

            return $out;
        }

        // fallback
        return (string) $value;
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
