<?php

// helper global para exponer la función vd() en espacio global
if (!function_exists('vd')) {
    function vd(mixed $label = '', ...$vars): void
    {
        try {
            \Versadumps\Versadumps\VersaDumps::getInstance()->vd($vars, $label);
        } catch (\Throwable) {
            // evitar que errores de configuración rompan scripts de usuario
            // se podría loguear o mostrar dependiendo del entorno
        }
    }
}
