# Changelog

All notable changes to this project will be documented in this file.

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
