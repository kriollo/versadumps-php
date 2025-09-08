# VersaDumps PHP
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg)](#)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](#)
[![Status](https://img.shields.io/badge/status-stable-brightgreen.svg)](#)

VersaDumps PHP es una pequeña librería para enviar "dumps" (estructuras de datos, mensajes, etc.) a un visualizador VersaDumps.

Este README explica cómo instalar y usar el paquete tanto desde el repositorio como vía Composer, cómo ejecutar el helper CLI para crear la configuración inicial y cómo integrar la función global `vd()` en tus proyectos.

## Requisitos

- PHP >= 8.1
- ext-json
- Composer

## Instalación

Puedes instalar el paquete de dos formas:

### 1) Instalar desde Composer (recomendado)

Desde tu proyecto, ejecuta:

```bash
composer require versadumps-php/versadumps-php
```

Composer descargará el paquete y sus dependencias.

Después de instalado, hay dos formas de inicializar el archivo de configuración `versadumps.yml`:

- Ejecutar el script instalado en `vendor/bin`:

```powershell
# Windows / pwsh
php vendor/bin/versadumps-init

# o si Composer >= 2.1
composer exec versadumps-init --
```

- O añadir un script en el `composer.json` de tu proyecto para ejecutar más cómodo:

```json
"scripts": {
  "versadumps-init": "vendor/bin/versadumps-init"
}
```

Y luego:

```bash
composer run-script versadumps-init
```

> Nota: `composer run-script versadumps-init` funciona solo si el script está definido en el composer.json del proyecto donde lo ejecutas.

### 2) Instalar desde el repositorio (desarrollo)

Clona el repositorio y, desde la raíz del proyecto, ejecuta:

```bash
git clone https://github.com/kriollo/versadumps-php.git
cd versadumps-php
composer install
```

Puedes ejecutar el bin localmente:

```powershell
php bin/versadumps-init
```

## Uso básico

El paquete expone una función global llamada `vd()` para facilitar su uso. Para usarlo en tu proyecto:

1. Asegúrate de requerir el autoload de Composer en tu script:

```php
require 'vendor/autoload.php';
```

2. Usa la función `vd()` en cualquier parte de tu código:

```php
vd("Usuario", ['nombre' => 'Juan', 'edad' => 30]);
vd("mensaje", 'Un mensaje simple');
vd("", $usuarios); // se envia nombre de la variable $usuarios
```

Internamente, `vd()` delega a un singleton `Versadumps\Versadumps\VersaDumps` que envía los datos a `http://{host}:{port}/data`. Los valores por defecto se toman desde `versadumps.yml`.

## Archivo de configuración `versadumps.yml`

El archivo debe ubicarse en el *working directory* (getcwd) del proceso PHP. Contiene dos claves mínimas:

```yaml
host: 127.0.0.1
port: 9191
```

Puedes crear este archivo usando el comando CLI `versadumps-init` descrito antes.

## Detalles técnicos

- La clase `VersaDumps` se implementa como singleton (`getInstance()`) para que la función global `vd()` pueda invocar siempre la misma instancia.
- Se añadió `src/helpers.php` y dicha ruta está registrada en `composer.json` en `autoload.files` para exponer la función global `vd()` automáticamente cuando se requiere `vendor/autoload.php`.
- Se añadió `bin/versadumps-init` y la propiedad `bin` en `composer.json` para exponer el ejecutable en `vendor/bin/` al instalar el paquete.

## Solución de problemas

- Error "Call to undefined function vd()":
  - Asegúrate de haber incluido `require 'vendor/autoload.php';` en tu script.
  - Verifica que `src/helpers.php` esté presente en el paquete y registrado en `composer.json` como `autoload.files`. Si instalaste la versión publicada del paquete, ejecuta `composer dump-autoload -o` en el proyecto consumidor.

- Error "Command 'versadumps-init' is not defined":
  - `composer run-script versadumps-init` solo funciona para scripts declarados en el composer.json del proyecto actual. Para usar el comando provisto por este paquete usa `php vendor/bin/versadumps-init` o `composer exec versadumps-init --`.
  - Si quieres `composer run-script versadumps-init` en tu proyecto, añade un script en tu composer.json que invoque `vendor/bin/versadumps-init`.

- Error "El archivo de configuración 'versadumps.yml' no se encuentra":
  - Ejecuta `php vendor/bin/versadumps-init` (o `php bin/versadumps-init` si trabajas desde el repo) para crear el archivo en el directorio actual.

## Contribuir

Pull requests y issues son bienvenidos. Sigue las guías de estilo de PHP y añade pruebas si agregas funciones nuevas.

## Licencia

MIT
