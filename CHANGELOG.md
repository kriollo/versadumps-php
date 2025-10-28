# Changelog

All notable changes to this project will be documented in this file.

## [2.2.0] - 2025-10-28
### Added
- **Patrón Builder con interfaz fluida**: La función `vd()` ahora retorna un objeto `VersaDumpsBuilder` que permite encadenar métodos de configuración
- **13 métodos de extensión del builder**:
  - `label(string)`: Define etiqueta personalizada explícita
  - `color(string)`: Establece color personalizado (10 colores disponibles)
  - `trace(int)`: Incluye stack trace con N niveles (default: 5)
  - `depth(int)`: Limita profundidad de serialización para estructuras complejas
  - `once()`: Ejecuta solo la primera vez (útil en loops)
  - `if(bool)`: Ejecución condicional (solo si true)
  - `unless(bool)`: Ejecución condicional (solo si false)
  - `send()`: Forzar ejecución inmediata sin esperar al destructor
  - **Métodos semánticos** para clasificación visual:
    - `info()`: Color azul para información general
    - `success()`: Color verde para operaciones exitosas
    - `warning()`: Color amarillo para advertencias
    - `error()`: Color rojo para errores críticos
    - `important()`: Color naranja para datos importantes
- **Sistema de metadata**: Nuevo campo opcional `metadata` en el payload que incluye `color`, `includeTrace` y `max_depth`
- **10 colores predefinidos**: red, green, blue, yellow, purple, orange, pink, cyan, gray, white
- **Ejecución lazy**: El builder ejecuta automáticamente en su destructor o con `send()` explícito
- **Cache para once()**: Sistema de cache basado en archivo:línea:label para evitar ejecuciones duplicadas
- **Documentación completa**:
  - `BUILDER_EXTENSIONS.md`: Documentación detallada de todas las extensiones
  - `SEMANTIC_METHODS.md`: Guía rápida de métodos semánticos
  - `PAYLOAD_SPECIFICATION.md`: Especificación técnica completa del payload JSON
- **Ejemplos avanzados**:
  - `example/advanced.php`: 10 ejemplos con todas las extensiones
  - `example/semantic-methods.php`: 7 ejemplos de métodos semánticos
  - `example/payload-examples.php`: 14 ejemplos de payloads para implementación del servidor

### Changed
- **Propiedades readonly**: `$host` y `$port` ahora son readonly (PHP 8.1+) para evitar mutabilidad
- **Valores por defecto mejorados**: Config con fallback a `127.0.0.1:9191` usando operador `??`
- **Firma del método vd()**: Ahora acepta parámetro `metadata` opcional: `vd(array $data = [], ?string $label = null, array $metadata = [])`
- **Backward compatible**: El estilo tradicional `vd("label", $data)` sigue funcionando sin cambios
- **Detección mejorada**: Mejor lógica para distinguir estilo tradicional vs moderno
- **README actualizado**: Documentación completa con ejemplos de todas las características

### Fixed
- Eliminadas variables no utilizadas en `processCallerFrame`: `$frame`, `$function`, `$class`
- Corregido nombre de clase en `example/index.php` (de `index` a `User`)
- Mejorada detección de estilo tradicional para evitar falsos positivos

### Performance
- **Ejecución condicional**: `if()` y `unless()` evitan procesamiento innecesario
- **Once cache**: Reduce overhead en loops grandes
- **Depth control**: Limita serialización de estructuras profundas

## [2.1.0] - 2025-09-08
### Added
- **Inferencia automática de nombres de variable**: Cuando no se proporciona etiqueta explícita, `vd()` ahora detecta automáticamente el nombre de la variable pasada
  - Soporte para variables simples: `vd("", $miVariable)` → label: `$miVariable`
  - Soporte para propiedades de objeto: `vd("", $obj->propiedad)` → label: `$obj->propiedad`
  - Soporte para elementos de array: `vd("", $array[indice])` → label: `$array[indice]`
  - Fallback seguro: expresiones complejas no detectadas no causan errores

### Changed
- **Refactorización de la lógica de detección**: Centralizada toda la inferencia de nombres de variable en el método `processCallerFrame`
- **Helper simplificado**: El helper global `vd()` ahora es más limpio y sin duplicación de código
- **Mejor separación de responsabilidades**: Una sola ubicación para la lógica de análisis de código fuente

### Fixed
- Eliminada duplicación de lógica entre helper global y método `processCallerFrame`
- Mejorada la detección del frame caller para reportar correctamente la función que invoca `vd()`

## [2.0.0] - 2025-09-02
### Added
- **Parser YAML propio**: Implementada clase `YamlParser` para reemplazar `symfony/yaml` y evitar conflictos de versiones en proyectos consumidores
  - Soporte completo para configuraciones básicas (key: value, arrays, comentarios)
  - Manejo de tipos: strings, integers, floats, booleans, null, arrays
  - Generación de archivos YAML desde arrays PHP
- Configuraciones de herramientas de desarrollo actualizadas para PHP 8.1+ con compatibilidad mejorada:
  - `php-cs-fixer`: Migrado a reglas `@PHP81Migration` y versiones compatibles (`^3.40`)
  - `rector`: Actualizado a sintaxis v1.x con `LevelSetList::UP_TO_PHP_81`
  - `laravel/pint`: Actualizado a versión `^1.15` compatible con PHP 8.1

### Removed
- **Dependencia symfony/yaml eliminada**: Reemplazada por parser propio para evitar conflictos de versiones
- Dependencias Symfony downgradeadas para mantener compatibilidad con PHP 8.1+

### Fixed
- Resueltos problemas de compatibilidad de platform requirements
- Configuración de Rector migrada de sintaxis v2.x a v1.x
- Excluido `example/index.php` del análisis de Rector para evitar conflictos con clases locales

### Changed
- Requirement de PHP mantenido en `>=8.1` para mayor compatibilidad
- Todas las herramientas de linting y refactoring funcionando correctamente
- Reducción significativa de dependencias externas (solo `ext-json` requerido)

## [1.2.3] - 2025-08-31
### Added
- Ajusta el nombre del archivo desde donde se produce la llamada de la funcion vd para una mejor experiencia en el desarrollo y uso de la herramienta versadumps Visualizer (actualizaciones en `src/VersaDumps.php` y `example/index.php`).

### Fixed
- Correcciones menores y mejoras en la lógica de normalización y ejemplos.

## [1.2.1] - 2025-08-31
### Added
- Inclusión de cambios locales finales: ajustes en `src/VersaDumps.php` y `example/index.php`.

### Fixed
- Ajustes menores en el ejemplo y comportamiento de `vd()`.

## [1.2.1] - 2025-08-29
### Added
- Soporte recursivo y seguro en la normalización de objetos (manejo de colecciones, DateTime, prevención de recursión y lectura de propiedades privadas/protegidas).

### Fixed
- Corregida clase en `example/index.php` y robustecimiento de la selección de frame.

## [1.1.0] - 2025-08-29
### Added
- Normalización automática de objetos en `vd()`: si el objeto define `toArray()` se usará; si implementa `JsonSerializable` se usará `jsonSerialize()`, y como fallback `get_object_vars()`.
- Selección mejorada del frame que disparó `vd()` en el payload (se omiten el helper global y la propia clase para reportar la función real, p.ej. `test`).
- `VERSADUMPS_DRY_RUN=1` para imprimir payloads en consola durante pruebas.

## [1.0.0] - 2025-08-29
### Added
- Singleton `VersaDumps` con `getInstance()` y helper global `vd()` (cargado vía `autoload.files`).
- CLI `bin/versadumps-init` para crear `versadumps.yml` en el proyecto consumidor.
- Detección robusta de `versadumps.yml` y localización de `vendor/autoload.php` en varios escenarios.
- Configuración inicial para php-cs-fixer, Laravel Pint y Rector (dev-dependencies).
- Archivos de packaging y documentación para publicación en Packagist.

### Fixed
- Múltiples ajustes de compatibilidad con proyectos consumidores y mejoras de UX del bin.
- Configuración de `rector.php` corregida y ejecuciones de dry-run completadas.



### Changed
- Ajustes menores y tests manuales para validar objetos con métodos.
