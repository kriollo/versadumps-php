<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/**
 * Este archivo muestra los payloads JSON completos que se envían al servidor VersaDumps
 * con cada una de las funcionalidades disponibles.
 *
 * Para capturar los payloads reales, puedes usar un proxy HTTP o modificar temporalmente
 * VersaDumps::vd() para hacer echo del payload antes de enviarlo.
 */

echo "=== PAYLOADS ENVIADOS AL SERVIDOR VERSADUMPS ===\n\n";

// Función helper para mostrar el payload esperado
function mostrarPayload(string $titulo, array $payload): void
{
    echo "📦 {$titulo}\n";
    echo str_repeat('=', 80) . "\n";
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "\n\n";
}

// 1. USO TRADICIONAL BÁSICO
$usuario = ['nombre' => 'Juan', 'edad' => 30];
// vd("Usuario", $usuario);

mostrarPayload('1. Uso tradicional básico: vd("Usuario", $usuario)', [
    'context' => [
        'variables' => [
            [
                'name' => 'usuario',
                'value' => ['nombre' => 'Juan', 'edad' => 30],
                'type' => 'array'
            ]
        ],
        'line' => 30,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 30,
        'caller' => '{main}'
    ],
    'label' => 'Usuario'
]);

// 2. AUTO-DETECCIÓN DE VARIABLE
$datosImportantes = ['id' => 1, 'status' => 'active'];
// vd($datosImportantes);

mostrarPayload('2. Auto-detección: vd($datosImportantes)', [
    'context' => [
        'variables' => [
            [
                'name' => 'datosImportantes',
                'value' => ['id' => 1, 'status' => 'active'],
                'type' => 'array'
            ]
        ],
        'line' => 55,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 55,
        'caller' => '{main}'
    ],
    'label' => 'datosImportantes'
]);

// 3. MÉTODO LABEL
// vd($usuario)->label('Usuario del sistema')->send();

mostrarPayload('3. Con label personalizado: vd($usuario)->label("Usuario del sistema")', [
    'context' => [
        'variables' => [
            [
                'name' => 'usuario',
                'value' => ['nombre' => 'Juan', 'edad' => 30],
                'type' => 'array'
            ]
        ],
        'line' => 77,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 77,
        'caller' => '{main}'
    ],
    'label' => 'Usuario del sistema'
]);

// 4. MÉTODO COLOR PERSONALIZADO
// vd($usuario)->color('purple')->send();

mostrarPayload('4. Color personalizado: vd($usuario)->color("purple")', [
    'context' => [
        'variables' => [
            [
                'name' => 'usuario',
                'value' => ['nombre' => 'Juan', 'edad' => 30],
                'type' => 'array'
            ]
        ],
        'line' => 98,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 98,
        'caller' => '{main}'
    ],
    'label' => 'usuario',
    'metadata' => [
        'color' => 'purple'
    ]
]);

// 5. MÉTODO INFO (semántico)
// vd($usuario)->info()->send();

mostrarPayload('5. Método info: vd($usuario)->info()', [
    'context' => [
        'variables' => [
            [
                'name' => 'usuario',
                'value' => ['nombre' => 'Juan', 'edad' => 30],
                'type' => 'array'
            ]
        ],
        'line' => 121,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 121,
        'caller' => '{main}'
    ],
    'label' => 'usuario',
    'metadata' => [
        'color' => 'blue'
    ]
]);

// 6. MÉTODO SUCCESS (semántico)
// vd($resultado)->success()->send();

mostrarPayload('6. Método success: vd($resultado)->success()', [
    'context' => [
        'variables' => [
            [
                'name' => 'resultado',
                'value' => ['status' => 'ok', 'message' => 'Operación exitosa'],
                'type' => 'array'
            ]
        ],
        'line' => 144,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 144,
        'caller' => '{main}'
    ],
    'label' => 'resultado',
    'metadata' => [
        'color' => 'green'
    ]
]);

// 7. MÉTODO WARNING (semántico)
// vd($alerta)->warning()->send();

mostrarPayload('7. Método warning: vd($alerta)->warning()', [
    'context' => [
        'variables' => [
            [
                'name' => 'alerta',
                'value' => ['stock' => 5, 'minimo' => 10],
                'type' => 'array'
            ]
        ],
        'line' => 167,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 167,
        'caller' => '{main}'
    ],
    'label' => 'alerta',
    'metadata' => [
        'color' => 'yellow'
    ]
]);

// 8. MÉTODO ERROR (semántico)
// vd($error)->error()->send();

mostrarPayload('8. Método error: vd($error)->error()', [
    'context' => [
        'variables' => [
            [
                'name' => 'error',
                'value' => ['code' => 500, 'message' => 'Error interno'],
                'type' => 'array'
            ]
        ],
        'line' => 190,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 190,
        'caller' => '{main}'
    ],
    'label' => 'error',
    'metadata' => [
        'color' => 'red'
    ]
]);

// 9. MÉTODO IMPORTANT
// vd($critico)->important()->send();

mostrarPayload('9. Método important: vd($critico)->important()', [
    'context' => [
        'variables' => [
            [
                'name' => 'critico',
                'value' => ['priority' => 'high', 'action' => 'required'],
                'type' => 'array'
            ]
        ],
        'line' => 213,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 213,
        'caller' => '{main}'
    ],
    'label' => 'critico',
    'metadata' => [
        'color' => 'orange'
    ]
]);

// 10. MÉTODO TRACE
// vd($debug)->trace(3)->send();

mostrarPayload('10. Con trace: vd($debug)->trace(3)', [
    'context' => [
        'variables' => [
            [
                'name' => 'debug',
                'value' => ['action' => 'test'],
                'type' => 'array'
            ]
        ],
        'line' => 236,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 236,
        'caller' => '{main}'
    ],
    'label' => 'debug',
    'metadata' => [
        'includeTrace' => 3
    ]
]);

// 11. MÉTODO DEPTH
// vd($deepObject)->depth(2)->send();

$deepObject = [
    'level1' => [
        'level2' => [
            'level3' => [
                'level4' => 'deep'
            ]
        ]
    ]
];

mostrarPayload('11. Con depth: vd($deepObject)->depth(2)', [
    'context' => [
        'variables' => [
            [
                'name' => 'deepObject',
                'value' => [
                    'level1' => [
                        'level2' => [
                            'level3' => '...' // Truncado por depth=2
                        ]
                    ]
                ],
                'type' => 'array'
            ]
        ],
        'line' => 268,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 268,
        'caller' => '{main}'
    ],
    'label' => 'deepObject',
    'metadata' => [
        'max_depth' => 2
    ]
]);

// 12. COMBINACIÓN DE MÉTODOS
// vd($pedido)->label('Pedido procesado')->success()->trace(5)->send();

$pedido = ['id' => 123, 'total' => 999.99];

mostrarPayload('12. Combinación: vd($pedido)->label("Pedido procesado")->success()->trace(5)', [
    'context' => [
        'variables' => [
            [
                'name' => 'pedido',
                'value' => ['id' => 123, 'total' => 999.99],
                'type' => 'array'
            ]
        ],
        'line' => 297,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 297,
        'caller' => '{main}'
    ],
    'label' => 'Pedido procesado',
    'metadata' => [
        'color' => 'green',
        'includeTrace' => 5
    ]
]);

// 13. OBJETO CON toArray()
class Usuario
{
    public function __construct(
        private string $nombre,
        private int $edad
    ) {}

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'edad' => $this->edad
        ];
    }
}

$usuarioObj = new Usuario('Ana', 25);
// vd($usuarioObj)->info()->send();

mostrarPayload('13. Objeto con toArray(): vd($usuarioObj)->info()', [
    'context' => [
        'variables' => [
            [
                'name' => 'usuarioObj',
                'value' => [
                    'nombre' => 'Ana',
                    'edad' => 25
                ],
                'type' => 'object',
                'class' => 'Usuario'
            ]
        ],
        'line' => 337,
        'file' => __FILE__
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 337,
        'caller' => '{main}'
    ],
    'label' => 'usuarioObj',
    'metadata' => [
        'color' => 'blue'
    ]
]);

// 14. STACK TRACE COMPLETO (ejemplo de estructura)
mostrarPayload('14. Stack trace completo (estructura del array de trace)', [
    'context' => [
        'variables' => [
            [
                'name' => 'data',
                'value' => ['test' => true],
                'type' => 'array'
            ]
        ],
        'line' => 365,
        'file' => __FILE__,
        'trace' => [
            [
                'file' => '/path/to/file.php',
                'line' => 42,
                'function' => 'procesarDatos',
                'class' => 'App\\Services\\DataProcessor',
                'type' => '->',
                'args' => ['arg1', 'arg2']
            ],
            [
                'file' => '/path/to/controller.php',
                'line' => 89,
                'function' => 'handle',
                'class' => 'App\\Controllers\\ApiController',
                'type' => '->',
                'args' => []
            ],
            [
                'file' => '/path/to/index.php',
                'line' => 15,
                'function' => 'run',
                'class' => 'App\\Application',
                'type' => '->',
                'args' => []
            ]
        ]
    ],
    'frame' => [
        'file' => __FILE__,
        'line' => 365,
        'caller' => 'App\\Services\\DataProcessor::procesarDatos'
    ],
    'label' => 'data',
    'metadata' => [
        'includeTrace' => 3
    ]
]);

// 15. TODOS LOS COLORES DISPONIBLES
echo "📌 COLORES DISPONIBLES Y SUS PAYLOADS\n";
echo str_repeat('=', 80) . "\n\n";

$colores = [
    'red' => 'Rojo - Errores críticos',
    'green' => 'Verde - Éxito / Operaciones completadas',
    'blue' => 'Azul - Información general',
    'yellow' => 'Amarillo - Advertencias',
    'purple' => 'Púrpura - Datos especiales',
    'orange' => 'Naranja - Importante / Alta prioridad',
    'pink' => 'Rosa - Destacados / UI',
    'cyan' => 'Cian - Datos de sistema',
    'gray' => 'Gris - Logs / Debug',
    'white' => 'Blanco - General / Default'
];

foreach ($colores as $color => $descripcion) {
    echo "  • {$color}: {$descripcion}\n";
    echo "    Payload: metadata.color = \"{$color}\"\n\n";
}

// 16. RESUMEN DE METADATA DISPONIBLE
echo "\n📋 RESUMEN DE CAMPOS METADATA DISPONIBLES\n";
echo str_repeat('=', 80) . "\n\n";

$metadataFields = [
    'color' => [
        'tipo' => 'string',
        'valores' => 'red, green, blue, yellow, purple, orange, pink, cyan, gray, white',
        'metodos' => 'color(string), info(), success(), warning(), error(), important()',
        'ejemplo' => '->color("purple") o ->success()'
    ],
    'includeTrace' => [
        'tipo' => 'integer',
        'valores' => 'Número de niveles de stack trace (default: 5)',
        'metodos' => 'trace(int)',
        'ejemplo' => '->trace(10)'
    ],
    'max_depth' => [
        'tipo' => 'integer',
        'valores' => 'Profundidad máxima de serialización',
        'metodos' => 'depth(int)',
        'ejemplo' => '->depth(3)'
    ]
];

foreach ($metadataFields as $campo => $info) {
    echo "🔹 {$campo}\n";
    echo "   Tipo: {$info['tipo']}\n";
    echo "   Valores: {$info['valores']}\n";
    echo "   Métodos: {$info['metodos']}\n";
    echo "   Ejemplo: vd(\$data){$info['ejemplo']}\n\n";
}

// 17. PAYLOAD MÁXIMO (todas las features combinadas)
echo "\n🎯 PAYLOAD COMPLETO CON TODAS LAS CARACTERÍSTICAS\n";
echo str_repeat('=', 80) . "\n";

$payloadCompleto = [
    'context' => [
        'variables' => [
            [
                'name' => 'pedido',
                'value' => [
                    'id' => 12345,
                    'cliente' => [
                        'nombre' => 'Juan Pérez',
                        'email' => 'juan@example.com'
                    ],
                    'items' => [
                        ['producto' => 'A', 'cantidad' => 2],
                        ['producto' => 'B', 'cantidad' => 1]
                    ],
                    'total' => 1500.00,
                    'status' => 'procesado'
                ],
                'type' => 'array'
            ]
        ],
        'line' => 450,
        'file' => '/var/www/app/Controllers/OrderController.php',
        'trace' => [
            [
                'file' => '/var/www/app/Controllers/OrderController.php',
                'line' => 450,
                'function' => 'procesarPedido',
                'class' => 'App\\Controllers\\OrderController',
                'type' => '->',
                'args' => [12345]
            ],
            [
                'file' => '/var/www/app/Routes/api.php',
                'line' => 25,
                'function' => 'handle',
                'class' => 'App\\Middleware\\ApiMiddleware',
                'type' => '->',
                'args' => []
            ],
            [
                'file' => '/var/www/public/index.php',
                'line' => 42,
                'function' => 'run',
                'class' => 'App\\Application',
                'type' => '->',
                'args' => []
            ]
        ]
    ],
    'frame' => [
        'file' => '/var/www/app/Controllers/OrderController.php',
        'line' => 450,
        'caller' => 'App\\Controllers\\OrderController::procesarPedido'
    ],
    'label' => 'Pedido procesado exitosamente',
    'metadata' => [
        'color' => 'green',
        'includeTrace' => 3,
        'max_depth' => 4
    ]
];

echo json_encode($payloadCompleto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo "\n\n";

echo "✅ Ejemplos completos generados!\n\n";
echo "📌 NOTAS IMPORTANTES PARA EL SERVIDOR VERSADUMPS:\n";
echo "   1. Todos los payloads se envían vía POST a http://host:port/data\n";
echo "   2. Content-Type: application/json\n";
echo "   3. El campo 'metadata' es OPCIONAL y solo aparece cuando se usan extensiones\n";
echo "   4. El campo 'trace' en context es OPCIONAL y solo aparece con trace()\n";
echo "   5. Los métodos if/unless/once se evalúan en PHP y NO se envían al servidor\n";
echo "   6. El método send() solo controla CUÁNDO se envía, no afecta el payload\n";
