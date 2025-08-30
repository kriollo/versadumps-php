<?php

// Incluye el autoload de Composer
require __DIR__ . '/../vendor/autoload.php';

use Versadumps\Versadumps\VersaDumps;

echo "Ejecutando ejemplo de VersaDumps...\n";

// Antes de usarlo, asegúrate de haber creado el archivo de configuración.
// Desde la carpeta 'php/', ejecuta en tu terminal:
// composer run-script versadumps-init

try {
    // Crea una instancia de la clase
    $dumper = new VersaDumps();

    // Envía los datos que quieras
    $miArray = ['nombre' => 'Juan', 'edad' => 30, 'ciudad' => 'Madrid'];

    $dumper->vd($miArray);
    echo " - Array enviado.\n";

    $otroDato = "Este es un string de prueba para VersaDumps.";
    $dumper->vd([
        "modo" => "info",
        "message" => $otroDato
    ]);
    echo " - String enviado.\n";

    echo "Ejemplo finalizado. Revisa tu aplicación para ver los datos.\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
