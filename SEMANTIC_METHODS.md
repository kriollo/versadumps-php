# Guía Rápida: Métodos Semánticos

## 📋 Resumen

Los métodos semánticos son atajos convenientes para establecer colores predefinidos según el tipo de mensaje.

## 🎨 Métodos Disponibles

### `info()` - Azul
**Uso:** Información general, debugging normal, datos informativos.

```php
vd($datos)->info();
vd($userSession)->info()->label('Sesión iniciada');
```

**Equivalente a:**
```php
vd($datos)->color('blue');
```

---

### `success()` - Verde
**Uso:** Operaciones exitosas, confirmaciones, resultados positivos.

```php
vd($resultado)->success();
vd($payment)->success()->label('Pago procesado');
```

**Equivalente a:**
```php
vd($datos)->color('green');
```

---

### `warning()` - Amarillo
**Uso:** Advertencias, situaciones que requieren atención, límites alcanzados.

```php
vd($diskUsage)->warning();
vd($memoryUsage)->warning()->label('Memoria al 90%');
```

**Equivalente a:**
```php
vd($datos)->color('yellow');
```

---

### `error()` - Rojo
**Uso:** Errores, excepciones, situaciones críticas.

```php
vd($error)->error();
vd($exception)->error()->trace()->label('Fallo crítico');
```

**Equivalente a:**
```php
vd($datos)->color('red');
```

---

## 💡 Ejemplos de Uso

### Ejemplo 1: Sistema de Logging
```php
function log($level, $message, $data) {
    $builder = vd($data)->label($message);

    match($level) {
        'info' => $builder->info(),
        'success' => $builder->success(),
        'warning' => $builder->warning(),
        'error' => $builder->error()
    };
}

log('info', 'Usuario autenticado', $user);
log('success', 'Archivo guardado', $file);
log('warning', 'Caché lleno', $cache);
log('error', 'Conexión fallida', $error);
```

### Ejemplo 2: Validación de Formulario
```php
$errors = validateForm($data);

if (empty($errors)) {
    vd($data)->success()->label('Formulario válido');
} else {
    vd($errors)->error()->label('Errores de validación');
}
```

### Ejemplo 3: Monitoreo de Rendimiento
```php
$metrics = getPerformanceMetrics();

if ($metrics['response_time'] > 1000) {
    vd($metrics)->warning()->label('Respuesta lenta');
} else {
    vd($metrics)->info()->label('Rendimiento normal');
}
```

### Ejemplo 4: Debugging Condicional
```php
$debug = env('APP_DEBUG');

vd($query)->info()->if($debug);
vd($result)->success()->if($debug);
vd($exception)->error()->trace();  // Siempre envía errores
```

### Ejemplo 5: En Loops con Once
```php
foreach ($items as $item) {
    if ($item->isInvalid()) {
        vd($item)->warning()->once()->label('Item inválido encontrado');
    }
}
```

## 🔗 Encadenamiento

Todos los métodos pueden encadenarse con otras opciones:

```php
// Con label
vd($data)->info()->label('Información de usuario');

// Con trace
vd($error)->error()->trace(5);

// Con condicionales
vd($query)->info()->if($debug);

// Con once
vd($item)->warning()->once();

// Múltiples opciones
vd($transaction)
    ->error()
    ->label('Transacción fallida')
    ->trace(3)
    ->if($debug);
```

## 🎯 Cuándo Usar Cada Método

| Método | Cuándo Usar | Ejemplos |
|--------|-------------|----------|
| `info()` | Datos normales, debugging general | Sesiones, queries, configuración |
| `success()` | Operaciones exitosas | Pagos, guardados, validaciones OK |
| `warning()` | Situaciones de atención | Límites, deprecated, carga alta |
| `error()` | Problemas y fallos | Excepciones, validación fallida, BD down |

## 🚀 Ventajas

1. **Código más legible**: `->error()` es más claro que `->color('red')`
2. **Consistencia**: Siempre los mismos colores para los mismos tipos
3. **Rápido**: Un método en lugar de recordar nombres de colores
4. **Mantenible**: Cambiar el esquema de colores en un solo lugar

## 📦 Payload

Los métodos semánticos agregan metadata de color al payload:

```json
{
  "context": [...],
  "frame": {...},
  "label": "Mi mensaje",
  "metadata": {
    "color": "blue"  // o "green", "yellow", "red"
  }
}
```

## ⚡ Performance

Los métodos semánticos son simples wrappers sin overhead adicional:

```php
// Esto:
vd($data)->info();

// Es lo mismo que:
vd($data)->color('blue');

// Pero más semántico y legible
```

---

**Tip:** Usa métodos semánticos en lugar de colores directos para código más mantenible y expresivo.
