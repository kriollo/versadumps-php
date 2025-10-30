<?php

namespace Versadumps\Versadumps;

use Exception;

class VersaDumps
{
    private readonly string $host;

    private readonly int $port;

    /** @var self|null */
    private static ?self $instance = null;

    /**
     * Constructor. Se deja público por compatibilidad, pero usar el singleton via getInstance() es preferible.
     */
    public function __construct()
    {
        if (self::$instance !== null) {
            @trigger_error(
                "Instanciación directa de VersaDumps está desaprobada. Usa VersaDumps::getInstance() en su lugar.",
                E_USER_DEPRECATED,
            );
        }

        // mantener referencia singleton a la última instancia creada
        self::$instance = $this;
        // Buscar el archivo de configuración en varias rutas comunes.
        $candidates = [
            getcwd() . "/versadumps.yml", // working dir del proceso
            __DIR__ . "/../versadumps.yml", // raíz del paquete
            __DIR__ . "/../../versadumps.yml", // posible ruta cuando está en vendor/<pkg>/src
            dirname(__DIR__, 2) . "/versadumps.yml", // subir más niveles
            // buscar en vendor del proyecto llamador
            getcwd() . "/vendor/versadumps-php/versadumps-php/versadumps.yml",
            getcwd() . "/vendor/versadumps-php/versadumps-php/src/versadumps.yml",
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

        $config = YamlParser::parseFile($configFile);
        $this->host = $config["host"] ?? "127.0.0.1";
        $this->port = $config["port"] ?? 9191;
    }

    /** Obtener la instancia singleton */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** Dump variádico de datos.
     * @param array<int,mixed> $data
     * @param string|null $label Etiqueta opcional
     * @param array<string,mixed> $metadata Metadata adicional (trace, color, max_depth, etc.)
     */
    public function vd(array $data = [], ?string $label = null, array $metadata = []): void
    {
        // recoger backtrace y rutas
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);
        $frame = $this->processCallerFrame($bt, null);
        // Normalizar/convertir objetos en el contexto
        $normalized = [];
        foreach ($data as $k => $v) {
            $normalized[] = self::normalizeValue($v);
        }

        $payload = [
            "context" => $normalized,
            "frame" => $frame,
            "label" => $label === null || $label === "" ? $frame["variableName"] : $label,
        ];

        // Agregar metadata si existe
        if (!empty($metadata)) {
            $payload["metadata"] = $metadata;
        }

        self::post(sprintf("http://%s:%d/data", $this->host, $this->port), json_encode($payload));
    }

    private function processCallerFrame($bt, array $callerFrame = null): array
    {
        $selfPath = realpath(__FILE__);
        $helpersPath = realpath(__DIR__ . "/helpers.php");
        $versaDumpsDir = realpath(__DIR__);

        // if a caller frame was provided by the global helper, prefer it
        $selected = null;
        $candidate = null;

        $fileNew = "";
        $lineNew = 0;
        $variableName = null;

        if ($callerFrame !== null && is_array($callerFrame)) {
            $selected = $callerFrame;
            $fileNew = $callerFrame["file"] ?? "";
            $lineNew = $callerFrame["line"] ?? 0;
            $variableName = $callerFrame["variable"] ?? null;
        } else {
            // Obtener el file, linea, variableName y demas datos del frame que llamo a vd
            // Se debe omitir los frames de esta clase y del helper global
            $projectRoot = realpath(getcwd()) ?: null;
            $firstValidFrame = null; // Primer frame válido del usuario
            $callerFileFrame = null; // Frame que contiene el file/line de la llamada real

            for ($i = 0, $len = count($bt); $i < $len; ++$i) {
                $f = $bt[$i];
                $file = isset($f["file"]) ? (realpath($f["file"]) ?: $f["file"]) : null;

                // Verificar si este frame debe saltarse (es de versaDumps)
                $shouldSkip = $this->isVersaDumpsFrame($f, $file, $selfPath, $helpersPath, $versaDumpsDir);

                if ($shouldSkip) {
                    continue;
                }

                // Este es un frame del código del usuario
                // Si aún no hemos encontrado el frame válido, este es
                if ($firstValidFrame === null) {
                    $firstValidFrame = $f;
                }

                // CASO ESPECIAL: Si este frame es __destruct() en archivo de usuario,
                // tomar su file/line pero NO usarlo para function/class
                $isDestructorFrame =
                    !empty($f["class"]) &&
                    $f["class"] === "VersaDumpsBuilder" &&
                    !empty($f["function"]) &&
                    $f["function"] === "__destruct";

                // Buscar el frame con file/line (el que contiene la línea de código real)
                if ($callerFileFrame === null && $file !== null && isset($f["line"])) {
                    $callerFileFrame = $f;
                    $fileNew = $file;
                    $lineNew = $f["line"];

                    // Si es __destruct(), continuar buscando para obtener la función real
                    if ($isDestructorFrame) {
                        continue; // No seleccionar este frame para function/class
                    }
                }

                // Seleccionar el frame adecuado para function/class
                // NO seleccionar si es __destruct de VersaDumpsBuilder
                if ($isDestructorFrame) {
                    continue; // Saltar este frame para la selección de function/class
                }

                // Priorizar frames del namespace app\
                if (!empty($f["class"]) && str_starts_with((string) $f["class"], "app\\")) {
                    if ($selected === null) {
                        $selected = $f;
                    }
                    // Si ya tenemos file/line del destructor, podemos parar aquí
                    if ($callerFileFrame !== null) {
                        break;
                    }
                }

                // Si el archivo está dentro del proyecto y no en vendor/, es buen candidato
                if ($file !== null && $projectRoot !== null) {
                    $lower = str_replace("\\", "/", strtolower((string) $file));
                    $rootLower = str_replace("\\", "/", strtolower($projectRoot));
                    if (str_starts_with($lower, $rootLower) && !str_contains($lower, "/vendor/")) {
                        if ($selected === null) {
                            $selected = $f;
                        }
                    }
                }

                // Guardar primer candidato válido como fallback
                if ($candidate === null && $file !== null) {
                    $candidate = $f;
                }

                // Si ya encontramos un frame seleccionado del namespace app\
                // y tenemos un callerFileFrame válido, podemos parar
                if (
                    $selected !== null &&
                    $callerFileFrame !== null &&
                    !empty($selected["class"]) &&
                    str_starts_with((string) $selected["class"], "app\\")
                ) {
                    break;
                }
            }

            // Fallbacks
            if ($selected === null && $candidate !== null) {
                $selected = $candidate;
            }

            if ($selected === null && $firstValidFrame !== null) {
                $selected = $firstValidFrame;
            }

            // Si no encontramos callerFileFrame pero tenemos firstValidFrame con file
            if (empty($fileNew) && $firstValidFrame !== null) {
                $fileNew = isset($firstValidFrame["file"])
                    ? (realpath($firstValidFrame["file"]) ?:
                    $firstValidFrame["file"])
                    : "";
                $lineNew = $firstValidFrame["line"] ?? 0;
            }

            // Inferir nombre de variable desde código fuente si no se proporcionó
            if ($variableName === null && $fileNew && $lineNew && is_readable($fileNew)) {
                $src = @file($fileNew, FILE_IGNORE_NEW_LINES);
                if ($src !== false && isset($src[$lineNew - 1])) {
                    $codeLine = $src[$lineNew - 1];

                    // Buscar la llamada a vd() en diferentes formatos:
                    // 1. vd($variable) - nuevo formato
                    // 2. vd($variable)->label('label') - nuevo formato con label
                    // 3. vd('label', $variable) - formato tradicional

                    // Intentar capturar variable en formato nuevo: vd($variable) o vd($variable)->...
                    if (
                        preg_match(
                            '/vd\s*\(\s*(\$[a-zA-Z_][a-zA-Z0-9_]*(?:->[a-zA-Z_][a-zA-Z0-9_]*)*|\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*)\s*\)(?:\s*->\s*\w+\s*\([^)]*\))?/u',
                            $codeLine,
                            $matches,
                        )
                    ) {
                        $variableName = $matches[1];
                    }
                    // Si no encontró en formato nuevo, intentar formato tradicional: vd('label', $variable)
                    elseif (
                        preg_match(
                            '/vd\s*\(\s*["\'][^"\']*["\']\s*,\s*(\$[a-zA-Z_][a-zA-Z0-9_]*(?:->[a-zA-Z_][a-zA-Z0-9_]*)*|\$[a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*)/u',
                            $codeLine,
                            $matches,
                        )
                    ) {
                        $variableName = $matches[1];
                    }
                }
            }
        }

        return [
            "file" => $fileNew,
            "line" => $lineNew,
            "function" => $selected["function"] ?? null,
            "class" => $selected["class"] ?? null,
            "variableName" => $variableName,
        ];
    }

    /**
     * Verifica si un frame pertenece a versaDumps y debe ser saltado
     */
    private function isVersaDumpsFrame(array $f, null|string $file, $selfPath, $helpersPath, $versaDumpsDir): bool
    {
        // 1. Verificar archivos de versaDumps
        if ($file !== null) {
            if ($selfPath !== false && $file === $selfPath) {
                return true;
            }
            if ($helpersPath !== false && $file === $helpersPath) {
                return true;
            }
            if ($versaDumpsDir !== false && str_starts_with((string) $file, $versaDumpsDir)) {
                return true;
            }
            if (str_contains((string) $file, "versadumps-php")) {
                return true;
            }
        }

        // 2. Verificar clases de versaDumps
        if (!empty($f["class"])) {
            $class = (string) $f["class"];

            // CASO ESPECIAL: Si la clase es VersaDumpsBuilder con __destruct en archivo de usuario,
            // este frame contiene la línea correcta donde se creó el builder (vd())
            if ($class === "VersaDumpsBuilder" && !empty($f["function"]) && $f["function"] === "__destruct") {
                // Verificar si el archivo es del usuario (no de versaDumps)
                $isUserFile =
                    $file !== null &&
                    !str_contains((string) $file, "versadumps-php") &&
                    ($versaDumpsDir === false || !str_starts_with((string) $file, $versaDumpsDir));

                if ($isUserFile) {
                    return false; // NO saltar, este es el frame correcto con la línea de vd()
                }
            }

            // Para las demás clases de versaDumps, sí saltar
            if (str_starts_with($class, "Versadumps\\") || $class === "VersaDumps" || $class === "VersaDumpsBuilder") {
                return true;
            }
        }

        // 3. Verificar funciones/métodos internos de versaDumps
        if (!empty($f["function"])) {
            $function = (string) $f["function"];
            $internalMethods = [
                "vd",
                "execute",
                "__destruct", // Se maneja arriba para el caso especial
                "label",
                "trace",
                "color",
                "depth",
                "once",
                "if",
                "unless",
                "important",
                "info",
                "success",
                "warning",
                "error",
                "send",
                "normalizeValue",
                "processCallerFrame",
                "isVersaDumpsFrame",
            ];

            if (in_array($function, $internalMethods, true)) {
                return true;
            }
        }

        return false;
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
                return ["__recursion__" => true, "__class__" => $value::class];
            }

            $seen[$id] = true;

            // Si define toArray(), preferirlo
            if (method_exists($value, "toArray")) {
                try {
                    $res = $value->toArray();

                    return self::normalizeValue($res, $seen);
                } catch (\Throwable) {
                }
            }

            // JsonSerializable
            if ($value instanceof \JsonSerializable) {
                try {
                    $res = $value->jsonSerialize();

                    return self::normalizeValue($res, $seen);
                } catch (\Throwable) {
                    // fallthrough
                }
            }

            // Usar reflexión para leer propiedades públicas/protegidas/privadas
            $out = ["__class__" => $value::class];

            try {
                $ref = new \ReflectionObject($value);
                foreach ($ref->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $name = $prop->getName();
                    // indicar visibilidad si no es pública
                    $prefix = "";
                    if ($prop->isProtected()) {
                        $prefix = "protected:";
                    }

                    if ($prop->isPrivate()) {
                        $prefix = "private:" . $prop->class . ":";
                    }

                    try {
                        $val = $prop->getValue($value);
                    } catch (\ReflectionException) {
                        $val = null;
                    }

                    $out[$prefix . $name] = self::normalizeValue($val, $seen);
                }
            } catch (\Throwable) {
                // si falla reflexión, caer al cast simple
                try {
                    $cast = (array) $value;
                    foreach ($cast as $k => $v) {
                        $out[$k] = self::normalizeValue($v, $seen);
                    }
                } catch (\Throwable) {
                    $out["__toString"] = method_exists($value, "__toString") ? (string) $value : null;
                }
            }

            return $out;
        }

        // fallback
        return (string) $value;
    }

    private static function post(string $url, string $body): bool|string
    {
        // prefer curl when available
        if (function_exists("curl_init")) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }

        // fallback to file_get_contents
        $opts = [
            "http" => [
                "method" => "POST",
                "header" => "Content-Type: application/json\r\n",
                "content" => $body,
                "timeout" => 1,
            ],
        ];
        $context = stream_context_create($opts);

        return @file_get_contents($url, false, $context);
    }
}

// helper global moved to src/helpers.php
