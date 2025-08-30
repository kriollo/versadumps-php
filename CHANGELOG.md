# Changelog

All notable changes to this project will be documented in this file.

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

## [1.1.0] - 2025-08-29
### Added
- Normalización automática de objetos en `vd()`: si el objeto define `toArray()` se usará; si implementa `JsonSerializable` se usará `jsonSerialize()`, y como fallback `get_object_vars()`.
- Selección mejorada del frame que disparó `vd()` en el payload (se omiten el helper global y la propia clase para reportar la función real, p.ej. `test`).
- `VERSADUMPS_DRY_RUN=1` para imprimir payloads en consola durante pruebas.

### Changed
- Ajustes menores y tests manuales para validar objetos con métodos.

## [1.2.1] - 2025-08-29
### Added
- Soporte recursivo y seguro en la normalización de objetos (manejo de colecciones, DateTime, prevención de recursión y lectura de propiedades privadas/protegidas).

### Fixed
- Corregida clase en `example/index.php` y robustecimiento de la selección de frame.

