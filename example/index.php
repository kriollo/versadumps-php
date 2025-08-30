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

        vd($miArray);
        echo " - Array enviado.\n";

        // Enviar un objeto de prueba (stdClass)
        $obj = new stdClass();
        $obj->nombre = 'María';
        $obj->roles = ['admin', 'editor'];
        $obj->meta = (object) ['activo' => true, 'visitas' => 42];

        vd($obj);
        echo " - Objeto enviado.\n";

        // Definir una clase con métodos y enviar una instancia
        class User
        {
            private string $name;
            private array $roles;

            public function __construct(string $name, array $roles = [])
            {
                $this->name = $name;
                $this->roles = $roles;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function getRoles(): array
            {
                return $this->roles;
            }

            public function toArray(): array
            {
                return ['name' => $this->name, 'roles' => $this->roles];
            }
        }

        $user = new User('Carlos', ['user', 'moderator']);
        vd($user);
        echo " - User objeto enviado.\n";
    }

    // llamar a la función test para que sea el caller en el backtrace
    test();

    $otroDato = 'Este es un string de prueba para VersaDumps.';
    vd([
        'modo' => 'info',
        'message' => $otroDato,
    ]);
    echo " - String enviado.\n";

    echo "Ejemplo finalizado. Revisa tu aplicación para ver los datos.\n";
} catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . "\n";
}
