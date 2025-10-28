# VersaDumps PHP
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg)](#)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](#)
[![Status](https://img.shields.io/badge/status-stable-brightgreen.svg)](#)
[![Version](https://img.shields.io/badge/version-2.2.0-blue.svg)](#)

VersaDumps PHP es una librería moderna para enviar "dumps" (estructuras de datos, mensajes, depuración, etc.) a un visualizador VersaDumps con características avanzadas como:

✨ **Características principales:**
- 🎨 **Patrón Builder** con interfaz fluida para configuración avanzada
- 🏷️ **Auto-detección de nombres de variables** para debugging más intuitivo
- 🎯 **Métodos semánticos** (info, success, warning, error) para clasificación visual
- 🔍 **Stack traces** opcionales para debugging profundo
- 🎨 **10 colores predefinidos** para categorización visual
- 🚀 **Ejecución condicional** (if, unless, once) para optimización
- ⚡ **Control de profundidad** para serialización eficiente de estructuras complejas

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

El paquete expone una función global llamada `vd()` que soporta dos estilos de uso: **tradicional** y **moderno** (builder pattern).

### 1. Asegúrate de requerir el autoload

```php
require 'vendor/autoload.php';
```

### 2. Estilo tradicional (compatible con versiones anteriores)

```php
// Con etiqueta explícita
vd("Usuario", ['nombre' => 'Juan', 'edad' => 30]);
vd("mensaje", 'Un mensaje simple');

// Auto-detección del nombre de variable
vd($usuarios); // Se envía con label "usuarios"
```

### 3. Estilo moderno (Builder Pattern) ⭐

```php
// Uso básico con auto-detección
vd($usuarios)->send();

// Con etiqueta personalizada
vd($data)->label('Datos importantes')->send();

// Métodos semánticos para clasificación visual
vd($user)->success(); // Color verde - operación exitosa
vd($error)->error();  // Color rojo - error crítico
vd($info)->info();    // Color azul - información general
vd($alert)->warning(); // Color amarillo - advertencia

// Stack trace para debugging
vd($variable)->trace(5); // Incluye 5 niveles de stack trace

// Control de profundidad para estructuras complejas
vd($deepObject)->depth(3); // Limita la serialización a 3 niveles

// Ejecución condicional
vd($debug)->if($isDevelopment); // Solo se ejecuta si la condición es true
vd($data)->unless($isProduction); // Solo se ejecuta si la condición es false

// Ejecutar solo una vez (útil en loops)
vd($item)->once(); // Solo se ejecuta en la primera iteración

// Colores personalizados
vd($data)->color('purple'); // 10 colores disponibles

// Combinación de métodos (fluent interface)
vd($order)
    ->label('Pedido procesado')
    ->success()
    ->trace(3)
    ->if($debug);

// Ejecución inmediata (sin esperar al destructor)
vd($criticalData)->error()->send();
```

## Métodos disponibles del Builder

| Método | Parámetros | Descripción |
|--------|-----------|-------------|
| `label(string)` | Etiqueta personalizada | Define una etiqueta explícita para el dump |
| `trace(int)` | Niveles (default: 5) | Incluye stack trace con N niveles |
| `color(string)` | Nombre del color | Establece color personalizado (red, green, blue, yellow, purple, orange, pink, cyan, gray, white) |
| `depth(int)` | Profundidad máxima | Limita la profundidad de serialización |
| `once()` | - | Ejecuta solo la primera vez (por archivo:línea:label) |
| `if(bool)` | Condición | Ejecuta solo si la condición es verdadera |
| `unless(bool)` | Condición | Ejecuta solo si la condición es falsa |
| `send()` | - | Ejecuta inmediatamente (sin esperar al destructor) |
| **Métodos semánticos** | | |
| `info()` | - | Atajo para `color('blue')` - información general |
| `success()` | - | Atajo para `color('green')` - operación exitosa |
| `warning()` | - | Atajo para `color('yellow')` - advertencia |
| `error()` | - | Atajo para `color('red')` - error crítico |
| `important()` | - | Atajo para `color('orange')` - dato importante |

### Ejemplos prácticos

```php
// Debugging de API
function procesarPedido($pedido) {
    vd($pedido)->label('Pedido recibido')->info();

    try {
        $resultado = $this->procesador->procesar($pedido);
        vd($resultado)->success();
        return $resultado;
    } catch (Exception $e) {
        vd($e)->error()->trace(10);
        throw $e;
    }
}

// Debugging condicional en producción
vd($query)->if(config('app.debug'))->label('SQL Query');

// Evitar spam en loops
foreach ($items as $item) {
    vd($item)->once(); // Solo muestra el primero
}

// Análisis de estructuras complejas
vd($deepNestedObject)->depth(2)->warning(); // Limita a 2 niveles

// Sistema de logging visual
vd($user)->info()->label('Usuario autenticado');
vd($payment)->success()->label('Pago procesado');
vd($stock)->warning()->label('Stock bajo');
vd($exception)->error()->trace(5)->label('Error crítico');
```

## Documentación detallada

Para más información sobre características avanzadas, consulta:

- **[BUILDER_EXTENSIONS.md](BUILDER_EXTENSIONS.md)** - Documentación completa de todas las extensiones del builder
- **[SEMANTIC_METHODS.md](SEMANTIC_METHODS.md)** - Guía rápida de métodos semánticos (info, success, warning, error)
- **[example/index.php](example/index.php)** - Ejemplos básicos de uso
- **[example/advanced.php](example/advanced.php)** - Ejemplos avanzados con todas las extensiones
- **[example/semantic-methods.php](example/semantic-methods.php)** - Ejemplos de métodos semánticos

Internamente, `vd()` delega a un singleton `Versadumps\Versadumps\VersaDumps` que envía los datos a `http://{host}:{port}/data`. Los valores por defecto se toman desde `versadumps.yml`.

## Archivo de configuración `versadumps.yml`

El archivo debe ubicarse en el *working directory* (getcwd) del proceso PHP. Contiene dos claves mínimas:

```yaml
host: 127.0.0.1
port: 9191
```

Puedes crear este archivo usando el comando CLI `versadumps-init` descrito antes.

## Detalles técnicos

- **Singleton Pattern**: La clase `VersaDumps` se implementa como singleton (`getInstance()`) para que la función global `vd()` pueda invocar siempre la misma instancia.
- **Builder Pattern**: La función `vd()` retorna un objeto `VersaDumpsBuilder` que permite encadenar métodos de configuración.
- **Lazy Execution**: El builder ejecuta automáticamente en su destructor, o puedes forzar ejecución inmediata con `send()`.
- **Auto-detección de variables**: Analiza el código fuente para inferir nombres de variables cuando no se proporciona etiqueta explícita.
- **Backward Compatible**: El estilo tradicional `vd("label", $data)` sigue funcionando sin cambios.
- **Helpers globales**: `src/helpers.php` está registrado en `composer.json` como `autoload.files` para exponer `vd()` automáticamente.
- **CLI Tool**: `bin/versadumps-init` está expuesto en `vendor/bin/` para crear configuración inicial.

## Características avanzadas

### Variable Name Inference
```php
$usuario = ['nombre' => 'Juan'];
vd($usuario); // Label automático: "usuario"

$this->datosImportantes = [1, 2, 3];
vd($this->datosImportantes); // Label automático: "datosImportantes"
```

### Once Cache (evitar duplicados)
```php
foreach ($items as $item) {
    vd($item)->once(); // Solo el primer ítem se envía
}

// La cache usa: archivo:línea:label como clave única
```

### Conditional Execution
```php
// Solo en desarrollo
vd($query)->if(config('app.debug'));

// Solo en producción
vd($metrics)->unless(config('app.debug'));
```

### Depth Control
```php
$deepObject = [
    'level1' => [
        'level2' => [
            'level3' => [
                'level4' => 'deep'
            ]
        ]
    ]
];

vd($deepObject)->depth(2); // Solo muestra hasta level2
```

### Stack Traces
```php
function problematicFunction() {
    vd($data)->trace(10); // Incluye 10 niveles del stack
}
```

## Payload estructura

El payload enviado al servidor VersaDumps tiene la siguiente estructura:

```json
{
  "context": {
    "variables": [...],
    "line": 42,
    "file": "/path/to/file.php"
  },
  "frame": {
    "file": "/path/to/file.php",
    "line": 42,
    "caller": "MyClass::myMethod"
  },
  "label": "Usuario",
  "metadata": {
    "color": "green",
    "includeTrace": 5,
    "max_depth": 3
  }
}
```

## Solución de problemas

### Error "Call to undefined function vd()"
- Asegúrate de haber incluido `require 'vendor/autoload.php';` en tu script.
- Verifica que `src/helpers.php` esté presente en el paquete y registrado en `composer.json` como `autoload.files`.
- Si instalaste la versión publicada del paquete, ejecuta `composer dump-autoload -o` en el proyecto consumidor.

### Error "Command 'versadumps-init' is not defined"
- `composer run-script versadumps-init` solo funciona para scripts declarados en el composer.json del proyecto actual.
- Para usar el comando provisto por este paquete usa `php vendor/bin/versadumps-init` o `composer exec versadumps-init --`.
- Si quieres `composer run-script versadumps-init` en tu proyecto, añade un script en tu composer.json que invoque `vendor/bin/versadumps-init`.

### Error "El archivo de configuración 'versadumps.yml' no se encuentra"
- Ejecuta `php vendor/bin/versadumps-init` (o `php bin/versadumps-init` si trabajas desde el repo) para crear el archivo en el directorio actual.

### El builder no ejecuta mi dump
- Si no usas `send()`, el builder ejecuta automáticamente en su destructor.
- Verifica que no haya un `return` antes de que se destruya el objeto.
- Usa `send()` para forzar ejecución inmediata si es necesario.

### Los métodos semánticos no muestran colores
- Los colores deben ser soportados por tu visualizador VersaDumps.
- Verifica que el payload incluya el campo `metadata.color` en la petición HTTP.
- Asegúrate de estar usando versión 2.2.0+ de la librería.

### Stack trace demasiado largo
- Usa `trace(N)` con un número menor de niveles, ej: `trace(3)`.
- Por defecto se incluyen 5 niveles si no especificas parámetro.

## Changelog

### v2.2.0 (2025-10-28)
- ✨ **Nuevo**: Patrón Builder con interfaz fluida
- ✨ **Nuevo**: 13 métodos de extensión (label, trace, color, depth, once, if, unless, send, info, success, warning, error, important)
- ✨ **Nuevo**: Métodos semánticos para clasificación visual
- ✨ **Nuevo**: Ejecución condicional (if, unless, once)
- ✨ **Nuevo**: Control de profundidad de serialización
- ✨ **Nuevo**: Stack traces opcionales
- ✨ **Nuevo**: 10 colores predefinidos
- 📚 **Nuevo**: Documentación completa (BUILDER_EXTENSIONS.md, SEMANTIC_METHODS.md)
- 📝 **Nuevo**: Ejemplos avanzados (advanced.php, semantic-methods.php)
- 🔧 **Mejora**: Propiedades readonly en VersaDumps (PHP 8.1+)
- 🔧 **Mejora**: Valores por defecto para configuración
- 🐛 **Fix**: Eliminación de variables no utilizadas
- ♻️ **Refactor**: Mejor detección de estilo tradicional vs moderno

### v2.1.0
- ✨ Auto-detección de nombres de variables
- 🔧 Mejoras en backtrace y caller frame

### v2.0.0
- ✨ Parser YAML integrado
- 🔧 Soporte para objetos con método `toArray()`

### v1.x
- ✨ Implementación inicial
- ✨ Patrón Singleton
- ✨ Función global `vd()`

## Contribuir

Pull requests y issues son bienvenidos. Sigue las guías de estilo de PHP y añade pruebas si agregas funciones nuevas.

## Licencia

MIT
