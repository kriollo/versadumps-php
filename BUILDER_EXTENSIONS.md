# VersaDumps Builder Pattern - Extensiones

Documentación completa de todas las extensiones disponibles en el builder pattern de VersaDumps.

## 📚 Tabla de Contenidos

- [Métodos Básicos](#métodos-básicos)
- [Colores y Semántica](#colores-y-semántica)
- [Stack Trace](#stack-trace)
- [Control de Ejecución](#control-de-ejecución)
- [Optimización](#optimización)
- [Ejemplos Completos](#ejemplos-completos)

## Métodos Básicos

### `label(string $label)`
Establece una etiqueta descriptiva para el dump.

```php
vd($usuario)->label('Usuario autenticado');
```

### `send()`
Ejecuta el dump inmediatamente sin esperar al destructor.

```php
vd($data)->label('Datos importantes')->send();
// Continúa ejecutando código...
```

## Colores y Semántica

### `color(string $color)`
Establece un color personalizado para el visualizador.

**Colores soportados:** `red`, `blue`, `green`, `yellow`, `orange`, `purple`, `pink`, `gray`, `cyan`, `magenta`

```php
vd($error)->label('Error crítico')->color('red');
vd($info)->label('Información')->color('blue');
```

### Métodos Semánticos

Atajos convenientes para colores comunes:

#### `important()`
Marca como importante (color rojo).

```php
vd($config)->important();
// Equivalente a: ->color('red')
```

#### `info()`
Marca como información (color azul).

```php
vd($datos)->info();
// Equivalente a: ->color('blue')
```

#### `success()`
Marca como éxito (color verde).

```php
vd($resultado)->success();
// Equivalente a: ->color('green')
```

#### `warning()`
Marca como advertencia (color amarillo).

```php
vd($alerta)->warning();
// Equivalente a: ->color('yellow')
```

#### `error()`
Marca como error (color rojo).

```php
vd($excepcion)->error();
// Equivalente a: ->color('red')
```

## Stack Trace

### `trace(int $limit = 10)`
Incluye el stack trace completo en el dump.

```php
function nivel3() {
    vd($data)->label('Desde nivel 3')->trace(5);
}

function nivel2() {
    nivel3();
}

function nivel1() {
    nivel2();
}

nivel1(); // El dump incluirá el stack trace de 5 niveles
```

**Parámetros:**
- `$limit` (opcional): Número máximo de frames a incluir. Default: 10

**Uso:**
- Ideal para debugging profundo
- Ayuda a entender el flujo de ejecución
- Útil para encontrar el origen de problemas

## Control de Ejecución

### `if(bool $condition)`
Envío condicional: solo ejecuta si la condición es verdadera.

```php
$debug = true;
vd($query)->if($debug)->label('SQL Query');
// Solo se envía si $debug es true
```

### `unless(bool $condition)`
Envío condicional inverso: solo ejecuta si la condición es falsa.

```php
$production = false;
vd($secretKey)->unless($production)->label('API Key');
// Solo se envía si $production es false
```

**Casos de uso:**
- Debugging condicional basado en flags
- Evitar dumps en producción
- Logging selectivo por entorno

### `once()`
Solo envía el dump la primera vez que se ejecuta.

```php
for ($i = 0; $i < 1000; $i++) {
    vd($item)->once()->label('Loop item');
}
// Solo se envía el primer item del loop
```

**Características:**
- Usa la posición en el código como clave única
- Útil en loops o código que se ejecuta múltiples veces
- No afecta el rendimiento en ejecuciones subsecuentes

## Optimización

### `depth(int $depth)`
Establece la profundidad máxima de serialización.

```php
$deepArray = [
    'level1' => [
        'level2' => [
            'level3' => [
                'level4' => 'muy profundo'
            ]
        ]
    ]
];

vd($deepArray)->depth(3)->label('Array profundo');
// Solo se serializarán 3 niveles
```

**Beneficios:**
- Evita dumps enormes de estructuras profundas
- Mejora el rendimiento
- Mantiene el dump legible

## Ejemplos Completos

### Ejemplo 1: Debugging de Transacciones

```php
function procesarTransaccion($transaction) {
    vd($transaction)
        ->label('Transacción recibida')
        ->info();

    if ($transaction['amount'] > 10000) {
        vd($transaction)
            ->label('Transacción de alto valor')
            ->important()
            ->trace();
    }

    $resultado = validarTransaccion($transaction);

    if ($resultado['success']) {
        vd($resultado)->success();
    } else {
        vd($resultado)->error()->trace();
    }
}
```

### Ejemplo 2: Debugging Condicional por Entorno

```php
$isDebug = env('APP_DEBUG', false);
$isDev = env('APP_ENV') === 'development';

vd($query)
    ->if($isDebug)
    ->label('Database Query')
    ->color('cyan');

vd($apiResponse)
    ->unless($isDev)
    ->label('API Response (solo producción)')
    ->warning();
```

### Ejemplo 3: Optimización en Loops

```php
foreach ($bigDataset as $index => $item) {
    // Solo muestra el primer item
    vd($item)->once()->label('Estructura del item');

    // Muestra solo si hay error
    if ($item['status'] === 'error') {
        vd($item)
            ->error()
            ->label("Error en item #{$index}");
    }
}
```

### Ejemplo 4: Encadenamiento Completo

```php
vd($userData)
    ->label('Datos de usuario completos')
    ->color('purple')
    ->trace(5)
    ->depth(4)
    ->if($debug)
    ->send();
```

## Compatibilidad

### Estilo Tradicional (Compatible)

```php
// Con label como primer parámetro
vd('Mi etiqueta', $variable);
vd('Múltiples', $var1, $var2, $var3);
```

### Estilo Moderno (Recomendado)

```php
// Con builder pattern
vd($variable)->label('Mi etiqueta');
vd($variable)->label('Test')->color('blue')->trace();
```

## Matriz de Métodos

| Método | Retorna | Ejecuta Inmediatamente | Encadenable |
|--------|---------|------------------------|-------------|
| `label()` | `self` | No | ✅ |
| `trace()` | `self` | No | ✅ |
| `color()` | `self` | No | ✅ |
| `depth()` | `self` | No | ✅ |
| `once()` | `self` | No | ✅ |
| `if()` | `self` | No | ✅ |
| `unless()` | `self` | No | ✅ |
| `important()` | `self` | No | ✅ |
| `info()` | `self` | No | ✅ |
| `success()` | `self` | No | ✅ |
| `warning()` | `self` | No | ✅ |
| `error()` | `self` | No | ✅ |
| `send()` | `self` | Sí | ✅ |

## Performance Tips

1. **Usa `once()` en loops**: Evita enviar miles de dumps idénticos
2. **Usa `depth()` para estructuras grandes**: Limita la profundidad de serialización
3. **Usa `if()` para debugging condicional**: Solo envía cuando realmente lo necesitas
4. **Combina métodos semánticos**: Son más rápidos que `color()` personalizado

## Próximas Extensiones (Roadmap)

- `timeout(int $seconds)`: Establece timeout para el envío
- `async()`: Envío asíncrono sin bloquear
- `batch()`: Agrupa múltiples dumps en un solo envío
- `filter(callable $callback)`: Filtra/transforma datos antes de enviar
- `context(array $extra)`: Agrega contexto adicional
- `memory()`: Incluye uso de memoria actual
- `performance()`: Incluye métricas de rendimiento

## Contribuir

Si tienes ideas para nuevas extensiones, abre un issue en GitHub o envía un PR.

---

**Versión:** 2.1.0+
**Licencia:** MIT
**Documentación completa:** [GitHub](https://github.com/kriollo/versadumps-php)
