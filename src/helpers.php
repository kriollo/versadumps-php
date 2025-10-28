<?php

/**
 * Clase builder para vd() que permite encadenamiento y flexibilidad para futuras opciones.
 *
 * Ejemplo de uso:
 *   vd($variable)                         - Auto-detecta nombre de variable
 *   vd($variable)->label('mi label')      - Con etiqueta explícita
 *   vd($variable)->trace()                - Incluye stack trace completo
 *   vd($variable)->color('red')           - Color personalizado en visualizador
 *   vd($variable)->depth(5)               - Profundidad máxima de serialización
 *   vd($variable)->once()                 - Solo envía la primera vez (útil en loops)
 *   vd($variable)->if(condition)          - Envío condicional
 *   vd('label', $variable)                - Estilo tradicional (compatible)
 */
class VersaDumpsBuilder
{
    private bool $executed = false;
    private ?string $label = null;
    private bool $includeTrace = false;
    private ?string $color = null;
    private ?int $maxDepth = null;
    private bool $onceOnly = false;
    private bool $shouldExecute = true;
    private static array $onceCache = [];

    public function __construct(private readonly array $variables = [])
    {
        // No auto-ejecutar, esperar a métodos de configuración o destructor
    }

    /**
     * Destructor: ejecutar dump con detección automática si no se ha ejecutado aún.
     */
    public function __destruct()
    {
        if (!$this->executed && count($this->variables) > 0 && $this->shouldExecute) {
            $this->execute();
        }
    }

    /**
     * Establece un label explícito.
     *
     * @param string $label Etiqueta descriptiva
     * @return self Para permitir encadenamiento
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Incluye el stack trace completo en el dump.
     * Útil para debugging profundo y entender el flujo de ejecución.
     *
     * @param int $limit Número máximo de frames a incluir (default: 10)
     * @return self Para permitir encadenamiento
     */
    public function trace(int $limit = 10): self
    {
        $this->includeTrace = true;
        $this->maxDepth = $limit;
        return $this;
    }

    /**
     * Establece un color personalizado para el visualizador.
     *
     * Colores soportados: red, blue, green, yellow, orange, purple, pink, gray
     *
     * @param string $color Nombre del color
     * @return self Para permitir encadenamiento
     */
    public function color(string $color): self
    {
        $validColors = ['red', 'blue', 'green', 'yellow', 'orange', 'purple', 'pink', 'gray', 'cyan', 'magenta'];
        $this->color = in_array(strtolower($color), $validColors, true) ? strtolower($color) : null;
        return $this;
    }

    /**
     * Establece la profundidad máxima de serialización de objetos/arrays.
     * Útil para evitar dumps muy grandes de estructuras profundas.
     *
     * @param int $depth Profundidad máxima (default: sin límite)
     * @return self Para permitir encadenamiento
     */
    public function depth(int $depth): self
    {
        $this->maxDepth = max(1, $depth);
        return $this;
    }

    /**
     * Solo envía el dump la primera vez que se ejecuta (útil en loops).
     * Usa el label o la posición del código como clave única.
     *
     * @return self Para permitir encadenamiento
     */
    public function once(): self
    {
        $this->onceOnly = true;
        return $this;
    }

    /**
     * Envío condicional: solo ejecuta si la condición es verdadera.
     *
     * @param bool $condition Condición para ejecutar el dump
     * @return self Para permitir encadenamiento
     */
    public function if(bool $condition): self
    {
        $this->shouldExecute = $condition;
        return $this;
    }

    /**
     * Alias para if() con condición negada.
     *
     * @param bool $condition Condición para NO ejecutar el dump
     * @return self Para permitir encadenamiento
     */
    public function unless(bool $condition): self
    {
        $this->shouldExecute = !$condition;
        return $this;
    }

    /**
     * Marca el dump como importante/destacado.
     * Equivalente a color('red') con una bandera adicional.
     *
     * @return self Para permitir encadenamiento
     */
    public function important(): self
    {
        $this->color = 'red';
        return $this;
    }

    /**
     * Marca el dump como información/debug normal.
     * Equivalente a color('blue').
     *
     * @return self Para permitir encadenamiento
     */
    public function info(): self
    {
        $this->color = 'blue';
        return $this;
    }

    /**
     * Marca el dump como éxito/confirmación.
     * Equivalente a color('green').
     *
     * @return self Para permitir encadenamiento
     */
    public function success(): self
    {
        $this->color = 'green';
        return $this;
    }

    /**
     * Marca el dump como advertencia.
     * Equivalente a color('yellow').
     *
     * @return self Para permitir encadenamiento
     */
    public function warning(): self
    {
        $this->color = 'yellow';
        return $this;
    }

    /**
     * Marca el dump como error/crítico.
     * Equivalente a color('red').
     *
     * @return self Para permitir encadenamiento
     */
    public function error(): self
    {
        $this->color = 'red';
        return $this;
    }

    /**
     * Ejecuta el dump inmediatamente (útil para forzar ejecución antes del destructor).
     *
     * @return self Para permitir encadenamiento
     */
    public function send(): self
    {
        $this->execute();
        return $this;
    }

    /**
     * Ejecuta el dump con todas las opciones configuradas.
     */
    private function execute(): void
    {
        if ($this->executed || !$this->shouldExecute) {
            return;
        }

        // Verificar si es once y ya se ejecutó
        if ($this->onceOnly) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $key = ($bt[0]['file'] ?? '') . ':' . ($bt[0]['line'] ?? 0) . ':' . ($this->label ?? '');

            if (isset(self::$onceCache[$key])) {
                $this->executed = true;
                return;
            }

            self::$onceCache[$key] = true;
        }

        try {
            $instance = \Versadumps\Versadumps\VersaDumps::getInstance();

            // Preparar metadata adicional
            $metadata = [];

            if ($this->includeTrace) {
                $metadata['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $this->maxDepth ?? 10);
            }

            if ($this->color !== null) {
                $metadata['color'] = $this->color;
            }

            if ($this->maxDepth !== null && !$this->includeTrace) {
                $metadata['max_depth'] = $this->maxDepth;
            }

            // Pasar metadata como parte del label si hay opciones
            $finalLabel = $this->label;
            if (!empty($metadata)) {
                $instance->vd($this->variables, $finalLabel, $metadata);
            } else {
                $instance->vd($this->variables, $finalLabel);
            }

            $this->executed = true;
        } catch (\Throwable) {
            // evitar que errores de configuración rompan scripts de usuario
        }
    }
}

// helper global para exponer la función vd() en espacio global
if (!function_exists('vd')) {
    /**
     * Función global vd() para enviar dumps a VersaDumps.
     *
     * Uso moderno (builder pattern):
     *   vd($variable)                              - Auto-detecta nombre de variable
     *   vd($variable)->label('mi label')           - Con etiqueta explícita
     *   vd($variable)->trace()                     - Con stack trace completo
     *   vd($variable)->color('red')                - Con color personalizado
     *   vd($variable)->important()                 - Marca como importante (rojo)
     *   vd($variable)->once()                      - Solo primera ejecución
     *   vd($variable)->if($debug)                  - Envío condicional
     *   vd($variable)->depth(3)                    - Profundidad máxima
     *   vd($variable)->label('test')->color('blue')->trace()  - Encadenado
     *
     * Uso tradicional (compatible):
     *   vd('label', $variable)                     - Con etiqueta como primer parámetro
     *   vd('label', $var1, $var2)                  - Múltiples variables con etiqueta
     *
     * @param mixed ...$args Variables a dumpear, o label + variables
     * @return VersaDumpsBuilder|null Builder para encadenar, o null si se ejecutó directamente
     */
    function vd(...$args): ?VersaDumpsBuilder
    {
        if (func_num_args() === 0) {
            return new VersaDumpsBuilder();
        }

        // Estilo tradicional: vd('label', $var1, $var2, ...)
        // Solo si el primer arg es string Y hay más de un argumento
        if (count($args) > 1 && is_string($args[0]) && !empty($args[0])) {
            $label = array_shift($args);

            try {
                \Versadumps\Versadumps\VersaDumps::getInstance()->vd($args, $label);
            } catch (\Throwable) {
                // evitar que errores de configuración rompan scripts de usuario
            }

            return null;
        }

        // Estilo moderno: vd($var1, $var2, ...)->label('...')
        // O cualquier caso que no sea el tradicional con label
        return new VersaDumpsBuilder($args);
    }
}
