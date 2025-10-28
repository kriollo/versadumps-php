<?php

/**
 * Ejemplo avanzado de VersaDumps con todas las extensiones del builder pattern
 */

require __DIR__ . '/../vendor/autoload.php';

echo "=== VersaDumps - Ejemplos Avanzados ===\n\n";

try {
    // Ejemplo 1: Labels y colores
    echo "1. Labels y colores personalizados:\n";
    $usuario = ['nombre' => 'Juan', 'email' => 'juan@example.com'];
    vd($usuario)->label('Usuario principal')->color('blue');
    echo "   ✓ Usuario con color azul\n";

    $error = ['code' => 500, 'message' => 'Error interno'];
    vd($error)->label('Error crítico')->color('red');
    echo "   ✓ Error con color rojo\n\n";

    // Ejemplo 2: Métodos semánticos
    echo "2. Métodos semánticos (important, info, success, warning, error):\n";
    $config = ['debug' => true, 'env' => 'production'];
    vd($config)->important();
    echo "   ✓ Config marcada como importante\n";

    $datos = ['items' => 50, 'page' => 1];
    vd($datos)->info();
    echo "   ✓ Datos marcados como info\n";

    $resultado = ['status' => 'ok', 'saved' => true];
    vd($resultado)->success();
    echo "   ✓ Resultado marcado como éxito\n";

    $alerta = ['disk_space' => '90%'];
    vd($alerta)->warning();
    echo "   ✓ Alerta marcada como advertencia\n\n";

    // Ejemplo 3: Stack trace
    echo "3. Incluir stack trace completo:\n";
    function nivel3()
    {
        $data = ['nivel' => 3];
        vd($data)->label('Desde nivel 3')->trace(5);
    }

    function nivel2()
    {
        nivel3();
    }

    function nivel1()
    {
        nivel2();
    }

    nivel1();
    echo "   ✓ Dump con stack trace de 5 niveles\n\n";

    // Ejemplo 4: Once (útil en loops)
    echo "4. Once - solo primera ejecución en loop:\n";
    for ($i = 0; $i < 5; $i++) {
        $item = ['iteration' => $i, 'value' => $i * 10];
        vd($item)->once()->label('Loop item');
    }
    echo "   ✓ Solo se envió el primer item del loop\n\n";

    // Ejemplo 5: Condicional
    echo "5. Envío condicional (if/unless):\n";
    $debug = true;
    $production = false;

    $debugData = ['query' => 'SELECT * FROM users', 'time' => '0.05s'];
    vd($debugData)->if($debug)->label('Query debug');
    echo "   ✓ Enviado porque \$debug = true\n";

    $prodData = ['secret' => 'api-key-123'];
    vd($prodData)->unless($production)->label('Secret data');
    echo "   ✓ Enviado porque \$production = false\n\n";

    // Ejemplo 6: Profundidad limitada
    echo "6. Profundidad limitada para estructuras grandes:\n";
    $deepArray = [
        'level1' => [
            'level2' => [
                'level3' => [
                    'level4' => [
                        'level5' => 'profundo',
                    ],
                ],
            ],
        ],
    ];
    vd($deepArray)->depth(3)->label('Array profundo (máx 3 niveles)');
    echo "   ✓ Solo se serializaron 3 niveles\n\n";

    // Ejemplo 7: Encadenamiento múltiple
    echo "7. Encadenamiento de múltiples opciones:\n";
    $transaction = [
        'id' => 'TXN-12345',
        'amount' => 1500.50,
        'status' => 'pending',
        'user' => ['id' => 42, 'name' => 'Alice'],
    ];
    vd($transaction)
        ->label('Transacción pendiente')
        ->color('yellow')
        ->trace(3)
        ->depth(2);
    echo "   ✓ Con label, color, trace y profundidad limitada\n\n";

    // Ejemplo 8: Send explícito
    echo "8. Envío explícito con send():\n";
    $cache = ['key' => 'user:123', 'ttl' => 3600];
    vd($cache)->label('Cache data')->color('cyan')->send();
    echo "   ✓ Enviado inmediatamente sin esperar destructor\n\n";

    // Ejemplo 9: Múltiples variables
    echo "9. Múltiples variables en un dump:\n";
    $userInfo = ['nombre' => 'María', 'edad' => 28, 'activo' => true];
    vd($userInfo)->label('Datos de usuario')->info();
    echo "   ✓ Múltiples datos en un solo dump\n\n";

    // Ejemplo 10: Combinación avanzada en función real
    echo "10. Caso de uso real - debugging de función:\n";

    function procesarPedido($pedido, $usuario)
    {
        vd($pedido)->label('Pedido recibido')->info();

        if (empty($pedido['items'])) {
            vd($pedido)->label('Pedido vacío')->error()->trace();

            return false;
        }

        $total = array_sum(array_column($pedido['items'], 'precio'));
        vd($total)->label('Total calculado')->success();

        return true;
    }

    $pedido = [
        'id' => 'PED-001',
        'items' => [
            ['nombre' => 'Producto A', 'precio' => 100],
            ['nombre' => 'Producto B', 'precio' => 250],
        ],
        'fecha' => date('Y-m-d H:i:s'),
    ];

    $usuario = ['id' => 1, 'nombre' => 'Carlos'];
    procesarPedido($pedido, $usuario);
    echo "   ✓ Función con múltiples dumps contextuales\n\n";

    echo "=== Ejemplos completados ===\n";
    echo "Revisa tu aplicación VersaDumps Visualizer para ver todos los dumps.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
