# VersaDumps - Especificación de Payload (v2.2.0)

Este documento especifica el formato exacto de los payloads JSON que la librería PHP envía al servidor VersaDumps.

## 📡 Endpoint HTTP

```
POST http://{host}:{port}/data
Content-Type: application/json
```

Por defecto:
- Host: `127.0.0.1`
- Port: `9191`

## 📦 Estructura del Payload

### Payload Básico (Mínimo)

```json
{
  "context": {
    "variables": [
      {
        "name": "nombreVariable",
        "value": "valor",
        "type": "string"
      }
    ],
    "line": 42,
    "file": "/path/to/file.php"
  },
  "frame": {
    "file": "/path/to/file.php",
    "line": 42,
    "caller": "{main}"
  },
  "label": "Etiqueta"
}
```

### Payload Completo (Todas las características)

```json
{
  "context": {
    "variables": [
      {
        "name": "nombreVariable",
        "value": { "dato": "complejo" },
        "type": "array"
      }
    ],
    "line": 42,
    "file": "/path/to/file.php",
    "trace": [
      {
        "file": "/path/to/caller.php",
        "line": 100,
        "function": "nombreFuncion",
        "class": "NombreClase",
        "type": "->",
        "args": ["arg1", "arg2"]
      }
    ]
  },
  "frame": {
    "file": "/path/to/file.php",
    "line": 42,
    "caller": "NombreClase::metodo"
  },
  "label": "Etiqueta personalizada",
  "metadata": {
    "color": "green",
    "includeTrace": 5,
    "max_depth": 3
  }
}
```

## 🔍 Campos del Payload

### 1. `context` (object, requerido)

Información sobre el contexto donde se ejecutó vd().

#### 1.1 `context.variables` (array, requerido)

Array de variables capturadas. Cada elemento contiene:

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `name` | string | ✅ | Nombre de la variable (inferido o "unknown") |
| `value` | mixed | ✅ | Valor de la variable (cualquier tipo JSON) |
| `type` | string | ✅ | Tipo de dato: "string", "integer", "float", "boolean", "array", "object", "null", "resource" |
| `class` | string | ❌ | Solo para type="object": nombre completo de la clase |

**Ejemplos:**

```json
// Variable simple
{
  "name": "usuario",
  "value": "Juan",
  "type": "string"
}

// Array
{
  "name": "datos",
  "value": {"id": 1, "nombre": "Test"},
  "type": "array"
}

// Objeto con toArray()
{
  "name": "modelo",
  "value": {"id": 1, "nombre": "Test"},
  "type": "object",
  "class": "App\\Models\\Usuario"
}
```

#### 1.2 `context.line` (integer, requerido)

Número de línea donde se llamó a `vd()`.

#### 1.3 `context.file` (string, requerido)

Ruta completa del archivo donde se llamó a `vd()`.

#### 1.4 `context.trace` (array, opcional)

Array de stack trace. **Solo presente cuando se usa `->trace(N)`**.

Cada elemento del trace contiene:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `file` | string | Ruta del archivo |
| `line` | integer | Número de línea |
| `function` | string | Nombre de la función |
| `class` | string | Nombre de la clase (si aplica) |
| `type` | string | Tipo de llamada: "->" (instancia), "::" (estático) |
| `args` | array | Argumentos de la función (simplificados) |

**Ejemplo:**

```json
"trace": [
  {
    "file": "/var/www/app/Services/DataService.php",
    "line": 42,
    "function": "procesarDatos",
    "class": "App\\Services\\DataService",
    "type": "->",
    "args": [123, "test"]
  },
  {
    "file": "/var/www/app/Controllers/ApiController.php",
    "line": 89,
    "function": "handle",
    "class": "App\\Controllers\\ApiController",
    "type": "->",
    "args": []
  }
]
```

### 2. `frame` (object, requerido)

Información del frame de ejecución actual.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `file` | string | Archivo donde se ejecutó vd() |
| `line` | integer | Línea donde se ejecutó vd() |
| `caller` | string | Función/método que llamó a vd() |

**Formato de `caller`:**
- Función global: `nombreFuncion`
- Método de instancia: `NombreClase::nombreMetodo`
- Script principal: `{main}`

### 3. `label` (string, requerido)

Etiqueta descriptiva del dump.

**Valores posibles:**
- Etiqueta explícita: `vd("Mi etiqueta", $data)` → `"Mi etiqueta"`
- Con builder: `vd($data)->label("Etiqueta")` → `"Etiqueta"`
- Auto-detectado: `vd($usuario)` → `"usuario"`
- Sin detectar: `vd($data)` → `"unknown"` (si falla la detección)

### 4. `metadata` (object, opcional)

Metadatos adicionales. **Solo presente cuando se usan métodos del builder**.

#### 4.1 `metadata.color` (string, opcional)

Color para visualización. **Solo presente cuando se usa `->color()` o métodos semánticos**.

**Valores válidos:**
- `"red"` - Errores críticos (método: `->error()`)
- `"green"` - Éxito (método: `->success()`)
- `"blue"` - Información (método: `->info()`)
- `"yellow"` - Advertencias (método: `->warning()`)
- `"orange"` - Importante (método: `->important()`)
- `"purple"` - Datos especiales
- `"pink"` - Destacados/UI
- `"cyan"` - Datos de sistema
- `"gray"` - Logs/Debug
- `"white"` - General/Default

**Ejemplo:**
```json
"metadata": {
  "color": "green"
}
```

#### 4.2 `metadata.includeTrace` (integer, opcional)

Número de niveles de stack trace solicitados. **Solo presente cuando se usa `->trace(N)`**.

- Default: 5 (si se usa `->trace()` sin parámetro)
- Mínimo: 1
- Máximo: Ilimitado (depende de la profundidad real del stack)

**Ejemplo:**
```json
"metadata": {
  "includeTrace": 10
}
```

#### 4.3 `metadata.max_depth` (integer, opcional)

Profundidad máxima de serialización. **Solo presente cuando se usa `->depth(N)`**.

- Default: Sin límite (si no se especifica)
- Mínimo: 1
- Uso típico: 2-5 para estructuras profundas

**Ejemplo:**
```json
"metadata": {
  "max_depth": 3
}
```

#### 4.4 Combinación de metadata

Todos los campos metadata pueden combinarse:

```json
"metadata": {
  "color": "green",
  "includeTrace": 5,
  "max_depth": 3
}
```

## 📊 Ejemplos Completos por Caso de Uso

### Caso 1: Dump básico tradicional

```php
vd("Usuario", ['nombre' => 'Juan', 'edad' => 30]);
```

```json
{
  "context": {
    "variables": [
      {
        "name": "usuario",
        "value": {"nombre": "Juan", "edad": 30},
        "type": "array"
      }
    ],
    "line": 15,
    "file": "/var/www/app/test.php"
  },
  "frame": {
    "file": "/var/www/app/test.php",
    "line": 15,
    "caller": "{main}"
  },
  "label": "Usuario"
}
```

### Caso 2: Auto-detección de variable

```php
$datosImportantes = ['id' => 1, 'status' => 'active'];
vd($datosImportantes);
```

```json
{
  "context": {
    "variables": [
      {
        "name": "datosImportantes",
        "value": {"id": 1, "status": "active"},
        "type": "array"
      }
    ],
    "line": 17,
    "file": "/var/www/app/test.php"
  },
  "frame": {
    "file": "/var/www/app/test.php",
    "line": 17,
    "caller": "{main}"
  },
  "label": "datosImportantes"
}
```

### Caso 3: Método semántico success

```php
vd($resultado)->success();
```

```json
{
  "context": {
    "variables": [
      {
        "name": "resultado",
        "value": {"status": "ok"},
        "type": "array"
      }
    ],
    "line": 20,
    "file": "/var/www/app/test.php"
  },
  "frame": {
    "file": "/var/www/app/test.php",
    "line": 20,
    "caller": "{main}"
  },
  "label": "resultado",
  "metadata": {
    "color": "green"
  }
}
```

### Caso 4: Con stack trace

```php
vd($debug)->trace(3);
```

```json
{
  "context": {
    "variables": [
      {
        "name": "debug",
        "value": {"action": "test"},
        "type": "array"
      }
    ],
    "line": 25,
    "file": "/var/www/app/Services/DataService.php",
    "trace": [
      {
        "file": "/var/www/app/Services/DataService.php",
        "line": 25,
        "function": "procesarDatos",
        "class": "App\\Services\\DataService",
        "type": "->",
        "args": []
      },
      {
        "file": "/var/www/app/Controllers/ApiController.php",
        "line": 50,
        "function": "handle",
        "class": "App\\Controllers\\ApiController",
        "type": "->",
        "args": []
      },
      {
        "file": "/var/www/public/index.php",
        "line": 10,
        "function": "run",
        "class": "App\\Application",
        "type": "->",
        "args": []
      }
    ]
  },
  "frame": {
    "file": "/var/www/app/Services/DataService.php",
    "line": 25,
    "caller": "App\\Services\\DataService::procesarDatos"
  },
  "label": "debug",
  "metadata": {
    "includeTrace": 3
  }
}
```

### Caso 5: Combinación completa

```php
vd($pedido)
    ->label('Pedido procesado')
    ->success()
    ->trace(5)
    ->depth(3);
```

```json
{
  "context": {
    "variables": [
      {
        "name": "pedido",
        "value": {
          "id": 12345,
          "cliente": {
            "nombre": "Juan Pérez",
            "email": "juan@example.com"
          },
          "total": 1500.00
        },
        "type": "array"
      }
    ],
    "line": 30,
    "file": "/var/www/app/Controllers/OrderController.php",
    "trace": [
      {
        "file": "/var/www/app/Controllers/OrderController.php",
        "line": 30,
        "function": "procesarPedido",
        "class": "App\\Controllers\\OrderController",
        "type": "->",
        "args": [12345]
      },
      {
        "file": "/var/www/app/Routes/api.php",
        "line": 25,
        "function": "handle",
        "class": "App\\Middleware\\ApiMiddleware",
        "type": "->",
        "args": []
      }
    ]
  },
  "frame": {
    "file": "/var/www/app/Controllers/OrderController.php",
    "line": 30,
    "caller": "App\\Controllers\\OrderController::procesarPedido"
  },
  "label": "Pedido procesado",
  "metadata": {
    "color": "green",
    "includeTrace": 5,
    "max_depth": 3
  }
}
```

### Caso 6: Objeto con toArray()

```php
class Usuario {
    public function toArray() {
        return ['nombre' => 'Ana', 'edad' => 25];
    }
}

$usuario = new Usuario();
vd($usuario)->info();
```

```json
{
  "context": {
    "variables": [
      {
        "name": "usuario",
        "value": {
          "nombre": "Ana",
          "edad": 25
        },
        "type": "object",
        "class": "Usuario"
      }
    ],
    "line": 45,
    "file": "/var/www/app/test.php"
  },
  "frame": {
    "file": "/var/www/app/test.php",
    "line": 45,
    "caller": "{main}"
  },
  "label": "usuario",
  "metadata": {
    "color": "blue"
  }
}
```

## 🎯 Notas de Implementación para el Servidor

### 1. Parseo del Payload

El servidor debe:
- ✅ Validar que sea JSON válido
- ✅ Verificar campos requeridos: `context`, `frame`, `label`
- ⚠️ Tratar `metadata` como opcional
- ⚠️ Tratar `context.trace` como opcional

### 2. Manejo de Metadata

```javascript
// Ejemplo en JavaScript/Node.js
function procesarPayload(payload) {
  const { context, frame, label, metadata } = payload;

  // Color (opcional)
  const color = metadata?.color || 'white'; // default

  // Trace (opcional)
  const hasTrace = context.trace !== undefined;
  const traceDepth = metadata?.includeTrace || 0;

  // Max depth (opcional)
  const maxDepth = metadata?.max_depth || Infinity;

  // Procesar variables
  context.variables.forEach(variable => {
    console.log(`Variable: ${variable.name} (${variable.type})`);
    if (variable.type === 'object') {
      console.log(`  Clase: ${variable.class}`);
    }
  });
}
```

### 3. Visualización de Colores

Mapeo sugerido de colores a estilos CSS:

```css
.vd-red { background-color: #ef4444; color: white; }
.vd-green { background-color: #10b981; color: white; }
.vd-blue { background-color: #3b82f6; color: white; }
.vd-yellow { background-color: #f59e0b; color: black; }
.vd-orange { background-color: #f97316; color: white; }
.vd-purple { background-color: #a855f7; color: white; }
.vd-pink { background-color: #ec4899; color: white; }
.vd-cyan { background-color: #06b6d4; color: black; }
.vd-gray { background-color: #6b7280; color: white; }
.vd-white { background-color: #f3f4f6; color: black; }
```

### 4. Renderizado de Stack Trace

El trace debe mostrarse en orden inverso (desde la raíz hasta la llamada actual):

```
public/index.php:10 → App\Application::run()
Controllers/ApiController.php:50 → App\Controllers\ApiController::handle()
Services/DataService.php:25 → App\Services\DataService::procesarDatos()
```

### 5. Campos que NO se envían al servidor

Los siguientes métodos del builder **NO generan campos en el payload**:
- `->once()` - Se evalúa en PHP, evita el envío duplicado
- `->if($condition)` - Se evalúa en PHP, si es false no se envía nada
- `->unless($condition)` - Se evalúa en PHP, si es true no se envía nada
- `->send()` - Solo controla el timing (inmediato vs destructor)

## 📚 Referencias

- **Código fuente**: `src/VersaDumps.php` - Método `vd()`
- **Builder**: `src/helpers.php` - Clase `VersaDumpsBuilder`
- **Ejemplos**: `example/payload-examples.php`
- **Documentación**: `README.md`, `BUILDER_EXTENSIONS.md`, `SEMANTIC_METHODS.md`

## 🔄 Versionado

Esta especificación corresponde a **VersaDumps PHP v2.2.0**.

Cambios futuros en el payload serán versionados y documentados aquí.
