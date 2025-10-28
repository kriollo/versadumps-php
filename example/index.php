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
        // Envía los datos que quieras
        $miArray = ['nombre' => 'Juan', 'edad' => 30, 'ciudad' => 'Madrid'];

        vd('array', $miArray);
        echo " - Array enviado.\n";

        // Enviar un objeto de prueba (stdClass)
        $obj = new stdClass();
        $obj->nombre = 'María';
        $obj->roles = ['admin', 'editor'];
        $obj->meta = (object) ['activo' => true, 'visitas' => 42];

        vd('objeto', $obj);
        echo " - Objeto enviado.\n";

        // Definir una clase con métodos y enviar una instancia
        class index
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

        $user = new index('Carlos', ['user', 'moderator']);
        vd('Usuario', $user);
        echo " - User objeto enviado.\n";
    }

    // llamar a la función test para que sea el caller en el backtrace
    test();


    echo "Ejemplo finalizado. Revisa tu aplicación para ver los datos.\n";
} catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . "\n";
}
