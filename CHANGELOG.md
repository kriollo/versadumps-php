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

