<?php

// Incluye el autoload de Composer
require __DIR__ . '/../vendor/autoload.php';
echo "Ejecutando ejemplo de VersaDumps...\n";

// Antes de usarlo, asegúrate de haber creado el archivo de configuración.
// Desde la carpeta 'php/', ejecuta en tu terminal:
// composer run-script versadumps-init

try {
    // Definir una función que llama a vd() para que el backtrace muestre 'test'
    function test()
    {
        echo "\n=== Ejemplos de uso de vd() ===\n\n";

        // Ejemplo 1: Uso tradicional con label explícito
        echo "1. Uso tradicional: vd('label', \$variable)\n";
        $miArray = ['nombre' => 'Juan', 'edad' => 30, 'ciudad' => 'Madrid'];
        vd('array', $miArray);
        echo "   ✓ Array enviado con label 'array'\n\n";

        // Ejemplo 2: Uso moderno con builder pattern
        echo "2. Uso moderno: vd(\$variable)->label('label')\n";
        $obj = new stdClass();
        $obj->nombre = 'María';
        $obj->roles = ['admin', 'editor'];
        $obj->meta = (object) ['activo' => true, 'visitas' => 42];
        vd($obj)->label('objeto personalizado');
        echo "   ✓ Objeto enviado con label personalizado\n\n";

        // Ejemplo 3: Auto-detección de nombre de variable
        echo "3. Auto-detección: vd(\$variable) sin label\n";
        $miVariable = ['dato' => 'importante', 'valor' => 123];
        vd($miVariable);
        echo "   ✓ Variable enviada con auto-detección de nombre\n\n";

        // Ejemplo 4: Clase con método toArray()
        echo "4. Objeto con toArray():\n";
        class User
        {
            private string $name;

            private array $roles;

            /**
             * @param mixed[] $roles
             */
            public function __construct(string $name, array $roles = [])
            {
                $this->name = $name;
                $this->roles = $roles;
            }

            public function getName(): string
            {
                return $this->name;
            }

            /**
             * @return mixed[]
             */
            public function getRoles(): array
            {
                return $this->roles;
            }

            /**
             * @return array<string, mixed[]>
             */
            public function toArray(): array
            {
                return ['name' => $this->name, 'roles' => $this->roles];
            }
        }

        $user = new User('Carlos', ['user', 'moderator']);
        vd($user)->label('usuario completo')->trace(5);
        echo "   ✓ User objeto enviado\n\n";
    }

    // llamar a la función test para que sea el caller en el backtrace
    test();

    echo "=== Ejemplo finalizado ===\n";
    echo "Revisa tu aplicación VersaDumps Visualizer para ver los datos.\n";
} catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . "\n";
}
