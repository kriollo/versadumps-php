<?php

/**
 * Clase builder local para permitir encadenamiento en vd()
 * No forma parte de la librería versadumps-php original.
 */
class helpers
{
    private bool $executed = false;

    public function __construct(private readonly array $variables = [])
    {
        // No auto-ejecutar, esperar a label() o destructor
    }

    /**
     * Destructor: ejecutar dump con label vacío si no se ha ejecutado aún.
     */
    public function __destruct()
    {
        if (!$this->executed && count($this->variables) > 0) {
            $this->dump();
        }
    }

    /**
     * Método mágico para usar el nombre del método como label.
     */
    public function __call(string $name, array $arguments = []): self
    {
        try {
            // Usar las variables almacenadas y el nombre del método como label
            \Versadumps\Versadumps\VersaDumps::getInstance()->vd($this->variables, $name);
            $this->executed = true;
        } catch (\Throwable) {
            // evitar que errores de configuración rompan scripts de usuario
        }

        return $this;
    }

    /**
     * Ejecuta el dump sin label (con label vacío).
     */
    public function dump(): self
    {
        if (!$this->executed) {
            try {
                \Versadumps\Versadumps\VersaDumps::getInstance()->vd($this->variables, '');
                $this->executed = true;
            } catch (\Throwable) {
                // evitar que errores de configuración rompan scripts de usuario
            }
        }

        return $this;
    }

    /**
     * Establece el label y ejecuta el dump usando la librería original.
     */
    public function label(string $label): self
    {
        try {
            \Versadumps\Versadumps\VersaDumps::getInstance()->vd($this->variables, $label);
            $this->executed = true;
        } catch (\Throwable) {
            // evitar que errores de configuración rompan scripts de usuario
        }

        return $this;
    }
}

// helper global para exponer la función vd() en espacio global
if (!function_exists('vd')) {
    function vd(...$args): helpers|null
    {
        // Si no hay argumentos, retornar builder vacío
        if (func_num_args() === 0) {
            return new helpers();
        }

        // Si hay argumentos, verificar si es uso tradicional con label
        if (count($args) > 1 && is_string($args[0])) {
            // Uso tradicional: vd('label', $var1, $var2, ...)
            $label = array_shift($args);

            try {
                \Versadumps\Versadumps\VersaDumps::getInstance()->vd($args, $label);
            } catch (\Throwable) {
                // evitar que errores de configuración rompan scripts de usuario
            }

            return null;
        }

        // Nuevo uso: vd($var1, $var2, ...)->label('label')
        return new helpers($args);
    }
}
