## Publicar en Packagist

Para publicar el paquete en Packagist y que otros proyectos puedan `composer require versadumps-php/versadumps-php`, sigue estos pasos:

1. Asegúrate de que el repositorio está en GitHub (u otro VCS público) y que `composer.json` tiene un `name` único.
2. Crea un tag semántico para la versión que quieras publicar. Ejemplo:

```bash
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

3. Regístrate o entra en https://packagist.org y pulsa en "Submit" para enviar el repositorio. Packagist monitorizará los tags y creará las versiones automáticamente.

4. En tu proyecto consumidor, instala con:

```bash
composer require versadumps-php/versadumps-php
```

Notas:
- Usa `composer validate` antes de publicar para comprobar el `composer.json`.
- Márchame la versión con un tag semántico; Packagist solo muestra releases con tags.
- Añadir `.gitattributes` con `export-ignore` ayuda a evitar que archivos de desarrollo y `vendor/` se incluyan en los artefactos del paquete.
