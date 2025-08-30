<?php

// helper global para exponer la función vd() en espacio global
if (!function_exists('vd')) {
    function vd(...$vars)
    {
        try {
            \Versadumps\Versadumps\VersaDumps::getInstance()->vd(...$vars);
        } catch (\Throwable $e) {
            // evitar que errores de configuración rompan scripts de usuario
            // se podría loguear o mostrar dependiendo del entorno
        }
    }
}
