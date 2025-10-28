# VersaDumps PHP - Release v2.2.0 🎉

## 📅 Fecha de lanzamiento: 28 de octubre de 2025

---

## 🚀 Características principales

### 1. **Patrón Builder con interfaz fluida**
La función `vd()` ahora retorna un objeto `VersaDumpsBuilder` que permite encadenar métodos:

```php
vd($data)
    ->label('Mi etiqueta')
    ->success()
    ->trace(5)
    ->depth(3);
```

### 2. **13 métodos de extensión**

#### Configuración básica:
- `label(string)` - Etiqueta personalizada
- `send()` - Ejecución inmediata

#### Visualización:
- `color(string)` - 10 colores disponibles
- `trace(int)` - Stack traces opcionales
- `depth(int)` - Control de profundidad

#### Control de flujo:
- `once()` - Ejecutar solo una vez (útil en loops)
- `if(bool)` - Ejecución condicional
- `unless(bool)` - Ejecución condicional inversa

#### Métodos semánticos:
- `info()` - Color azul 🔵
- `success()` - Color verde 🟢
- `warning()` - Color amarillo 🟡
- `error()` - Color rojo 🔴
- `important()` - Color naranja 🟠

### 3. **Sistema de metadata**
Nuevo campo `metadata` en el payload JSON:

```json
{
  "metadata": {
    "color": "green",
    "includeTrace": 5,
    "max_depth": 3
  }
}
```

### 4. **10 colores predefinidos**
`red`, `green`, `blue`, `yellow`, `purple`, `orange`, `pink`, `cyan`, `gray`, `white`

---

## 📦 Archivos nuevos

### Documentación:
- ✅ `BUILDER_EXTENSIONS.md` - Documentación completa de extensiones
- ✅ `SEMANTIC_METHODS.md` - Guía de métodos semánticos
- ✅ `PAYLOAD_SPECIFICATION.md` - Especificación técnica del payload

### Ejemplos:
- ✅ `example/advanced.php` - 10 ejemplos avanzados
- ✅ `example/semantic-methods.php` - 7 ejemplos semánticos
- ✅ `example/payload-examples.php` - 14 ejemplos de payloads

---

## 🔧 Mejoras técnicas

### Código más robusto:
- ✅ Propiedades `readonly` en VersaDumps (`$host`, `$port`)
- ✅ Valores por defecto con operador `??`
- ✅ Eliminación de variables no utilizadas

### Mejor detección:
- ✅ Lógica mejorada para distinguir estilo tradicional vs moderno
- ✅ Fallback seguro en auto-detección de variables

### Performance:
- ✅ Cache para `once()` evita ejecuciones duplicadas
- ✅ `if()`/`unless()` evitan procesamiento innecesario
- ✅ `depth()` limita serialización de estructuras profundas

---

## 🔄 Compatibilidad

### ✅ Backward Compatible
El estilo tradicional sigue funcionando sin cambios:

```php
// Estilo tradicional (v1.x, v2.0, v2.1)
vd("Usuario", $usuario);

// Estilo moderno (v2.2+)
vd($usuario)->info();
```

### ✅ Requisitos
- PHP >= 8.1
- ext-json

---

## 📊 Comparación de versiones

| Característica | v2.1.0 | v2.2.0 |
|---------------|--------|--------|
| Builder Pattern | ❌ | ✅ |
| Métodos semánticos | ❌ | ✅ (5) |
| Stack traces | ❌ | ✅ |
| Colores | ❌ | ✅ (10) |
| Ejecución condicional | ❌ | ✅ |
| Control de profundidad | ❌ | ✅ |
| Auto-detección variables | ✅ | ✅ |
| Metadata en payload | ❌ | ✅ |

---

## 📚 Ejemplos de uso

### Ejemplo 1: Métodos semánticos
```php
vd($user)->info();           // Azul - información
vd($result)->success();      // Verde - éxito
vd($stock)->warning();       // Amarillo - advertencia
vd($exception)->error();     // Rojo - error
```

### Ejemplo 2: Debugging con trace
```php
function procesarPedido($pedido) {
    vd($pedido)->label('Pedido recibido')->trace(5);

    try {
        $resultado = procesar($pedido);
        vd($resultado)->success();
    } catch (Exception $e) {
        vd($e)->error()->trace(10);
    }
}
```

### Ejemplo 3: Optimización con once()
```php
foreach ($items as $item) {
    vd($item)->once(); // Solo muestra el primero
}
```

### Ejemplo 4: Condicional
```php
vd($query)->if(config('app.debug'));
vd($metrics)->unless(config('app.debug'));
```

### Ejemplo 5: Combinación total
```php
vd($order)
    ->label('Pedido procesado')
    ->success()
    ->trace(3)
    ->depth(2)
    ->if($debug);
```

---

## 🎯 Para implementadores del servidor VersaDumps

### Endpoint HTTP:
```
POST http://127.0.0.1:9191/data
Content-Type: application/json
```

### Campos del payload:

**Siempre presentes:**
- `context` (object)
  - `variables` (array)
  - `line` (integer)
  - `file` (string)
- `frame` (object)
- `label` (string)

**Opcionales:**
- `context.trace` (array) - Solo con `->trace()`
- `metadata` (object) - Solo con builder methods
  - `metadata.color` (string)
  - `metadata.includeTrace` (integer)
  - `metadata.max_depth` (integer)

Ver `PAYLOAD_SPECIFICATION.md` para detalles completos.

---

## 📖 Documentación

### Archivos actualizados:
- ✅ `README.md` - Documentación completa con ejemplos
- ✅ `CHANGELOG.md` - Historial de cambios detallado
- ✅ `composer.json` - Versión 2.2.0

### Archivos nuevos:
- 📄 `BUILDER_EXTENSIONS.md` - Extensiones del builder
- 📄 `SEMANTIC_METHODS.md` - Métodos semánticos
- 📄 `PAYLOAD_SPECIFICATION.md` - Especificación técnica
- 📄 `RELEASE_v2.2.0.md` - Este archivo

---

## 🔗 Enlaces

- **Repositorio**: https://github.com/kriollo/versaDumps
- **Issues**: https://github.com/kriollo/versaDumps/issues
- **Packagist**: https://packagist.org/packages/versadumps-php/versadumps-php

---

## 👏 Agradecimientos

Gracias a todos los que han contribuido al desarrollo de VersaDumps PHP.

---

## 🚀 Próximas versiones (Roadmap)

Características planificadas para futuras versiones:

- `timeout(int)` - Control de timeout HTTP
- `async()` - Envío asíncrono no bloqueante
- `batch()` - Agrupar múltiples dumps
- `filter(callable)` - Transformar datos antes de enviar
- `context(array)` - Metadata adicional de contexto
- `memory()` - Incluir uso de memoria
- `performance()` - Métricas de timing

---

**¡Disfruta VersaDumps PHP v2.2.0!** 🎉
