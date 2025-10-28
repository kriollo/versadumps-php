<?php

/**
 * Ejemplo específico de métodos semánticos: info, warning, error, success
 */

require __DIR__ . '/../vendor/autoload.php';

echo "=== Ejemplos de Métodos Semánticos ===\n\n";

try {
    // 1. INFO - Para información general, debugging normal
    echo "1. INFO - Información general:\n";
    $userSession = [
        'user_id' => 123,
        'username' => 'john.doe',
        'logged_at' => date('Y-m-d H:i:s'),
        'ip' => '192.168.1.100'
    ];
    vd($userSession)->info();
    echo "   ✓ Sesión de usuario (color azul)\n\n";

    // 2. SUCCESS - Para operaciones exitosas
    echo "2. SUCCESS - Operación exitosa:\n";
    $paymentResult = [
        'transaction_id' => 'TXN-9876',
        'amount' => 299.99,
        'status' => 'completed',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    vd($paymentResult)->success();
    echo "   ✓ Pago procesado correctamente (color verde)\n\n";

    // 3. WARNING - Para advertencias y situaciones que requieren atención
    echo "3. WARNING - Advertencia:\n";
    $diskUsage = [
        'total' => '500GB',
        'used' => '450GB',
        'available' => '50GB',
        'percentage' => '90%',
        'warning' => 'Espacio en disco bajo'
    ];
    vd($diskUsage)->warning();
    echo "   ✓ Advertencia de espacio en disco (color amarillo)\n\n";

    // 4. ERROR - Para errores y situaciones críticas
    echo "4. ERROR - Error crítico:\n";
    $errorDetails = [
        'error_code' => 'ERR_500',
        'message' => 'Internal Server Error',
        'file' => '/app/controllers/UserController.php',
        'line' => 142,
        'trace_preview' => 'UserController->store() -> validateUser()'
    ];
    vd($errorDetails)->error();
    echo "   ✓ Error crítico reportado (color rojo)\n\n";

    // 5. Combinación con otros métodos
    echo "5. Combinación de métodos semánticos con otras opciones:\n\n";

    // INFO + LABEL
    echo "   5.1. Info con label personalizado:\n";
    $apiRequest = ['endpoint' => '/api/users', 'method' => 'GET'];
    vd($apiRequest)->info()->label('API Request');
    echo "       ✓ Request con label e info\n\n";

    // SUCCESS + TRACE
    echo "   5.2. Success con stack trace:\n";
    function processOrder($order)
    {
        // Simular procesamiento exitoso
        vd($order)->success()->trace(3)->label('Orden procesada');
    }
    processOrder(['order_id' => 'ORD-123', 'total' => 150.00]);
    echo "       ✓ Éxito con trace\n\n";

    // WARNING + ONCE (útil en loops)
    echo "   5.3. Warning con once (solo primera vez en loop):\n";
    for ($i = 0; $i < 5; $i++) {
        $item = ['iteration' => $i, 'memory_usage' => '85%'];
        vd($item)->warning()->once()->label('Alto uso de memoria');
    }
    echo "       ✓ Solo se envió la primera advertencia\n\n";

    // ERROR + IF (condicional)
    echo "   5.4. Error condicional:\n";
    $validationFailed = true;
    $validationErrors = [
        'email' => 'Invalid format',
        'password' => 'Too short'
    ];
    vd($validationErrors)->error()->if($validationFailed)->label('Validation Errors');
    echo "       ✓ Error enviado solo si validación falló\n\n";

    // 6. Caso de uso real: Sistema de logging por nivel
    echo "6. Caso de uso real - Sistema de logging:\n\n";

    function logMessage($level, $message, $context = [])
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];

        $builder = vd($logEntry)->label("Log: $message");

        // Aplicar color según nivel
        match ($level) {
            'info' => $builder->info(),
            'success' => $builder->success(),
            'warning' => $builder->warning(),
            'error' => $builder->error(),
            default => $builder->info()
        };
    }

    logMessage('info', 'Usuario inició sesión', ['user_id' => 42]);
    logMessage('success', 'Archivo subido correctamente', ['filename' => 'document.pdf']);
    logMessage('warning', 'Caché casi lleno', ['usage' => '95%']);
    logMessage('error', 'Fallo en conexión a BD', ['host' => 'db.example.com']);

    echo "       ✓ Sistema de logging con colores automáticos\n\n";

    // 7. Comparación visual de todos los métodos
    echo "7. Comparación visual de todos los métodos semánticos:\n\n";

    $sampleData = ['ejemplo' => 'datos', 'timestamp' => time()];

    echo "   Enviando el mismo dato con diferentes métodos:\n";
    vd($sampleData)->info()->label('Método: info()');
    echo "   - info() enviado\n";

    vd($sampleData)->success()->label('Método: success()');
    echo "   - success() enviado\n";

    vd($sampleData)->warning()->label('Método: warning()');
    echo "   - warning() enviado\n";

    vd($sampleData)->error()->label('Método: error()');
    echo "   - error() enviado\n\n";

    echo "=== Ejemplos completados ===\n";
    echo "Revisa VersaDumps Visualizer para ver las diferencias de color:\n";
    echo "  - INFO: Azul\n";
    echo "  - SUCCESS: Verde\n";
    echo "  - WARNING: Amarillo\n";
    echo "  - ERROR: Rojo\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
