<?php

declare(strict_types=1);

namespace Versadumps\Versadumps;

/**
 * Parser YAML simple para archivos de configuración básicos
 * Reemplaza symfony/yaml para evitar conflictos de versiones.
 */
class YamlParser
{
    /**
     * Parse un archivo YAML simple
     * Soporta:
     * - key: value (strings, integers, floats, booleans, null)
     * - Arrays simples con - item
     * - Comentarios con #.
     */
    public static function parseFile(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException("Archivo YAML no encontrado: {$filename}");
        }

        $content = file_get_contents($filename);
        if ($content === false) {
            throw new \RuntimeException("No se pudo leer el archivo: {$filename}");
        }

        return self::parse($content);
    }

    /**
     * Parse contenido YAML desde string.
     */
    public static function parse(string $yaml): array
    {
        $result = [];
        $lines = explode("\n", $yaml);
        $arrayKey = null;

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);

            // Saltar líneas vacías y comentarios
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Arrays simples con guión
            if (str_starts_with($line, '- ')) {
                if ($arrayKey === null) {
                    throw new \InvalidArgumentException('Array item sin clave en línea ' . ($lineNumber + 1));
                }

                if (!isset($result[$arrayKey])) {
                    $result[$arrayKey] = [];
                }

                $value = trim(substr($line, 2));
                $result[$arrayKey][] = self::parseValue($value);
                continue;
            }

            // Key: value pairs
            if (str_contains($line, ':')) {
                $parts = explode(':', $line, 2);
                $key = trim($parts[0]);
                $value = isset($parts[1]) ? trim($parts[1]) : '';

                if ($value === '') {
                    // Preparar para array
                    $arrayKey = $key;
                    $result[$key] = [];
                } else {
                    $arrayKey = null;
                    $result[$key] = self::parseValue($value);
                }
                continue;
            }

            // Línea no reconocida
            throw new \InvalidArgumentException('Sintaxis YAML no válida en línea ' . ($lineNumber + 1) . ": {$line}");
        }

        return $result;
    }

    /**
     * Genera contenido YAML desde array PHP
     * Útil para crear archivos de configuración.
     */
    public static function dump(array $data, int $inline = 2): string
    {
        $yaml = '';

        foreach ($data as $key => $value) {
            $yaml .= self::dumpValue($key, $value, 0);
        }

        return $yaml;
    }

    /**
     * Convierte un valor string a su tipo PHP apropiado.
     */
    private static function parseValue(string $value): mixed
    {
        $value = trim($value);

        // Null values
        if ($value === 'null' || $value === '~' || $value === '') {
            return null;
        }

        // Boolean values
        if (in_array(strtolower($value), ['true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array(strtolower($value), ['false', 'no', 'off'], true)) {
            return false;
        }

        // Numeric values
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        // Quoted strings
        if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        // Regular strings
        return $value;
    }

    /**
     * Genera una línea YAML para una clave/valor.
     */
    private static function dumpValue(string $key, mixed $value, int $level): string
    {
        $indent = str_repeat('  ', $level);

        if (is_array($value)) {
            if (empty($value)) {
                return "{$indent}{$key}: []\n";
            }

            // Array asociativo vs numérico
            if (array_keys($value) !== range(0, count($value) - 1)) {
                // Array asociativo
                $result = "{$indent}{$key}:\n";
                foreach ($value as $subKey => $subValue) {
                    $result .= self::dumpValue((string) $subKey, $subValue, $level + 1);
                }

                return $result;
            }
            // Array numérico
            $result = "{$indent}{$key}:\n";
            foreach ($value as $item) {
                $result .= "{$indent}  - " . self::formatScalarValue($item) . "\n";
            }

            return $result;
        }

        return "{$indent}{$key}: " . self::formatScalarValue($value) . "\n";
    }

    /**
     * Formatea un valor escalar para YAML.
     */
    private static function formatScalarValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_string($value)) {
            // Escapar strings que contienen caracteres especiales
            if (
                preg_match('/[:\[\]{}#&*!|>\'"%@`]/', $value)
                || in_array(strtolower($value), ['true', 'false', 'null', 'yes', 'no', 'on', 'off'])
            ) {
                return '"' . str_replace('"', '\\"', $value) . '"';
            }

            return $value;
        }

        return (string) $value;
    }
}
